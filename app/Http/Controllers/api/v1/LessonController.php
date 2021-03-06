<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\api\v1\LessonPostRequest;
use App\Lesson;
use App\LessonCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class LessonController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $status = Input::get('status');
        $lessons = Lesson::orderBy('lesson_title', 'asc')
            ->with('status:status_id,status_label')
            ->with('courses')
            ->with(['categories' => function($query){
                $query->with('category:category_id,category_label');
            }])
            ->where('lesson_title', 'LIKE', "$this->q%")
            ->select('lesson_id', 'lesson_title', 'lesson_first_challenge_id', 'lesson_content_version', 'lesson_paradigm_id', 'lesson_level_of_difficulty_id',
                'lesson_status_id', 'created_at');
        if($status) $lessons = $lessons->where('lesson_status_id', $status);
        $lessons = $lessons->paginate($this->size);
        return response($lessons, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(LessonPostRequest $request)
    {
        $categories = $request->get('categories');
        $lesson = Lesson::create($request->except(['categories']));
        if($lesson->lesson_id){
            if($categories && count($categories) > 0){
                foreach ($categories as $c){
                    $lesson->categories()->save(new LessonCategory(['category_id' => $c['category_id']]));
                }
            }
        }
        return response($lesson, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $lesson = Lesson::find($id);
        $lesson->load('categories');
        return response($lesson, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(LessonPostRequest $request, $id)
    {
        $categories = $request->get('categories');
        $lesson = Lesson::find($id);
        $lesson->update($request->except(['categories']));
        $lesson->categories()->delete();
        foreach ($categories as $c){
            $lesson->categories()->save(new LessonCategory(['category_id' => $c['category_id']]));
        }
        return response($lesson, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $lesson = Lesson::find($id);
        $lesson->delete();
        return response(null, 204);
    }

    public function destroyMany(Request $request){
        if($request->get('ids')){
            Lesson::whereIn('lesson_id', $request->get('ids'))->delete();
        }
        return response(null, 204);
    }

    public function setFirstChallenge(Request $request){
        $lesson = Lesson::find($request->get('lessonId'));
        if($lesson){
            $lesson->lesson_first_challenge_id = $request->get('challengeId');
            $lesson->save();
        }
        return response(null, 204);
    }
}
