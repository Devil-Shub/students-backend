<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Validator;
use App\User;
use App\Subject;

class StudentController extends Controller
{
    public function __construct()
    {
    }

    /**
     * create student
     */
    public function createStudent(Request $request)
    {
        //validate request
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'class' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'The given data was invalid.',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            //begin transactions
            DB::beginTransaction();

            //create new student
            $student = new User([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'class' => $request->class
            ]);

            //if student save than save subjects as well
            if ($student->save()) {
                foreach ($request->studentDetails as $subject) {
                    $subject = new Subject([
                        'user_id' => $student->id,
                        'subject' => $subject['subject'],
                        'marks' => $subject['marks']
                    ]);

                    //save subject
                    $subject->save();
                }
            }

            //commit now
            DB::commit();
            //return success response
            return response()->json([
                'status' => true,
                'message' => 'Student saved successfully!',
                'data' => []
            ], 200);
        } catch (\Exception $e) {
            //rollback
            DB::rollBack();
            //make log of errors
            Log::error(json_encode($e->getMessage()));
            //return with error
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'data' => []
            ], 500);
        }
    }

    /**
     * list student
     */
    public function getStudentList()
    {
        return response()->json([
            'status' => true,
            'message' => 'Student saved successfully!',
            'data' => Subject::with('student')->get()
        ], 200);
    }

    /**
     * delete student
     */
    public function deleteStudent(Request $request)
    {
        try {

            User::whereId($request->student_id)->delete();

            //return success response
            return response()->json([
                'status' => true,
                'message' => 'Student deleted successfully!',
                'data' => []
            ], 200);
        } catch (\Exception $e) {
            //make log of errors
            Log::error(json_encode($e->getMessage()));
            //return with error
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'data' => []
            ], 500);
        }
    }

    /**
     * fetch student
     */
    public function getStudentById(Request $request)
    {
        try {
            //return success response
            return response()->json([
                'status' => true,
                'message' => 'Student details!',
                'data' => User::with('subjects')->whereId($request->student_id)->first()
            ], 200);
        } catch (\Exception $e) {
            //make log of errors
            Log::error(json_encode($e->getMessage()));
            //return with error
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'data' => []
            ], 500);
        }
    }

    /**
     * update student
     */
    public function updateStudent(Request $request)
    {
        //validate request
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'class' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'The given data was invalid.',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            //begin transactions
            DB::beginTransaction();

            //create new student
            $student = User::whereId($request->student_id)->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'class' => $request->class
            ]);

            Subject::whereUserId($request->student_id)->delete();

            //if student save than save subjects as well
            if ($student) {
                foreach ($request->studentDetails as $subject) {
                    $subject = new Subject([
                        'user_id' => $request->student_id,
                        'subject' => $subject['subject'],
                        'marks' => $subject['marks']
                    ]);

                    //save subject
                    $subject->save();
                }
            }

            //commit now
            DB::commit();
            //return success response
            return response()->json([
                'status' => true,
                'message' => 'Student saved successfully!',
                'data' => []
            ], 200);
        } catch (\Exception $e) {
            //rollback
            DB::rollBack();
            //make log of errors
            Log::error(json_encode($e->getMessage()));
            //return with error
            return response()->json([
                'status' => false,
                'message' => 'Internal server error!',
                'data' => []
            ], 500);
        }
    }

}
