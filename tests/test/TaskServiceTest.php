<?php

namespace test;

use core\application\ExternalTaskDTO;
use core\domain\entity\Task;
use core\domain\TaskLinkRepositoryInterface;
use core\domain\TaskRepositoryInterface;
use core\infrastructure\service\TaskService;
use Mockery;
use PHPUnit\Framework\TestCase;

class TaskServiceTest extends TestCase
{
    private TaskService $taskService;
    private $taskRepositoryMock;
    private $taskLinkRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->taskRepositoryMock = Mockery::mock(TaskRepositoryInterface::class);
        $this->taskLinkRepositoryMock = Mockery::mock(TaskLinkRepositoryInterface::class);

        $this->taskService = new TaskService(
            $this->taskRepositoryMock,
            $this->taskLinkRepositoryMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGetOrCreateSourceCreatesNewTask()
    {
        $dto = new ExternalTaskDTO('external_1', '123', 'Test Task', false);

        $this->taskRepositoryMock
            ->shouldReceive('findBySystemAndId')
            ->with('external_1', '123')
            ->andReturnNull();

        $task = $this->taskService->getOrCreateSource($dto);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('external_1', $task->getExternalSystem());
        $this->assertEquals('123', $task->getExternalId());
        $this->assertEquals('Test Task', $task->getName());
        $this->assertFalse($task->isCompleted());
    }

    public function testSaveSourceCallsRepository()
    {
        $task = Mockery::mock(Task::class);

        $this->taskRepositoryMock
            ->shouldReceive('save')
            ->once()
            ->with($task);

        $this->taskService->saveSource($task);
    }

    public function testSaveTargetHandlesTransaction()
    {
        $sourceTask = Mockery::mock(Task::class);
        $targetTask = Mockery::mock(Task::class);

        $this->taskLinkRepositoryMock->shouldReceive('beginTransaction')->once();
        $this->taskRepositoryMock->shouldReceive('save')->once()->with($targetTask);
        $this->taskLinkRepositoryMock->shouldReceive('findBySourceAndTarget')->andReturnNull();
        $this->taskLinkRepositoryMock->shouldReceive('updateTaskLink')->once();
        $this->taskLinkRepositoryMock->shouldReceive('commit')->once();

        $this->taskService->saveTarget($sourceTask, $targetTask);
    }

    public function testDeleteTargetHandlesTransaction()
    {
        $sourceTask = Mockery::mock(Task::class);
        $targetTask = Mockery::mock(Task::class);

        $targetTask->shouldReceive('setDeleted')->once();

        $this->taskLinkRepositoryMock->shouldReceive('beginTransaction')->once();
        $this->taskRepositoryMock->shouldReceive('save')->once()->with($targetTask);
        $this->taskLinkRepositoryMock->shouldReceive('findBySourceAndTarget')->andReturnNull();
        $this->taskLinkRepositoryMock->shouldReceive('updateTaskLink')->once();
        $this->taskLinkRepositoryMock->shouldReceive('commit')->once();

        $this->taskService->deleteTarget($sourceTask, $targetTask);
    }
}