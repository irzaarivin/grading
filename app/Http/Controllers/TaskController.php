<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\redis;
use App\Models\Task;


class TaskController extends Controller
{
    protected $statusList = null, 
        $lastList = null,
        $redisTaskKey = "tasks";

    public function create(Request $request) {
        $task = [
            "name" => $request->name,
            "status" => $request->status,
            "startTime" => $request->startTime,
            "endTime" => $request->endTime,
            "user_id" => $request->userId,
            "parentId" => $request->parentId
        ];

        $newTask = Task::create($task);

        Redis::set($this->redisTaskKey . ":task-" . $newTask->id, $newTask->toJson());

        $allTask = Task::get()->toJson();
        Redis::set($this->redisTaskKey . ":all", $allTask);

        return response()->json(['status' => 'success', 'newTask' => $newTask]);
    }

    public function task(Request $request) {
        $task = Task::find($request->id);

        if($task == null) {
            return response()->json(["status" => "Data not Found"], 404);
        }

        return response()->json($task);
    }

    public function tasks() {
        $cachedTask = Redis::get("ERP:tasks:all");

        if($cachedTask) return response()->json(["total" => "Banyak", "data" => $cachedTask]);

        $tasks = Task::all();
        $total = Task::count();

        Redis::set($this->redisTaskKey . ":all", $tasks);

        return response()->json(["total" => $total, "data" => $tasks]);
    }

    protected function getChild($data, $id) {
        $childs = Task::get()->where('parentId', $id);
        $data->child = $childs;

        if($childs) {
            $this->statusList = true;
            $this->lastList = $$childs;
            return $data;
        }

        $this->statusList = false;
        return false;
    }

    protected function getChildArr($datas, $id) {
        foreach($datas as $data)
        $childs = Task::get()->where('parentId', $id);
        $data->child = $childs;
        return $data;
    }

    public function taskDetail(Request $request) {
        $task = Task::find($request->id);
        $cek = $this->getChild($task, $task->id);

        if(!$cek) return $task;

        do {
            $cek = $this->getChild($task, $task->id);
        }
        while($this->statusList == true);


        if($cek != null) {
            do {
                if(is_array($cek)) {
                    foreach($cek as $data) {
                        $data = $this->getChild($data, $data->id);
                    }
                } else {
                    $cek = $this->getChild($task, $task->id);
                }
            }
            while($cek != null);
        }

        return response()->json($cek);
    }

    public function update(Request $request) {
        $task = Task::find($request->id)->update($request->data);
        if($task) {
            $updatedTask = Task::find($request->id);
            Redis::set($this->redisTaskKey . ":task-" . $updatedTask->id, $updatedTask->toJson());
            return response()->json(["status" => "Success Updated"], 200);
        }
        return response()->json(["status" => "Failed Updated"], 500);
    }

    public static function consumeRabbit($message) {
       Redis::set("rabbit", $message);
    }
}
