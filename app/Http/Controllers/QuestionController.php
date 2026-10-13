<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeQuestionStatusRequest;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\User;
use App\Services\QuestionService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    use ApiResponseTrait;

    protected QuestionService $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = [
            'monHocId' => $request->query('monHocId'),
            'chuongId' => $request->query('chuongId'),
            'doKhoId' => $request->query('doKhoId'),
            'nguoiTaoId' => $request->query('nguoiTaoId'),
            'status' => $request->query('status'),
            'keyword' => $request->query('keyword'),
            'bank' => $request->query('bank'),
        ];

        $data = $this->questionService->getAll($filters);
        return $this->success($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuestionRequest $request)
    {
        $data = $this->questionService->add($request->validated());
        return $this->success($data, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $cauhoi)
    {
        $cauHoi = $this->questionService->getOne($cauhoi);
        return $this->success($cauHoi);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuestionRequest $request, Question $cauhoi)
    {
        $data = $this->questionService->update($request->validated(), $cauhoi);
        return $this->success($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $cauhoi)
    {
        $result = $this->questionService->delete($cauhoi);
        return $this->success($result);
    }

    public function changeStatus(ChangeQuestionStatusRequest $request, Question $cauhoi)
    {
        $data = $this->questionService->changeStatus($cauhoi, $request->validated()['status']);
        return $this->success($data);
    }

    public function copyFromPublic(Request $request, Question $cauhoi)
    {
        $validated = $request->validate([
            'nguoiTaoId' => ['required', 'integer', 'exists:users,id'],
        ]);

        $data = $this->questionService->copyFromPublic($cauhoi, (int) $validated['nguoiTaoId']);
        return $this->success($data, 201);
    }

    public function getWithPrivate(User $user){
        $data = $this->questionService->getWithPrivate($user->id);
        return $this->success($data);
    }

    public function getAllPublic(){
        $data = $this->questionService->getAllPublic();
        return $this->success($data);
    }

    public function getAllOfUser(User $user){
        $data = $this->questionService->getAllOfUser($user);
        return $this->success($data);
    }
}

