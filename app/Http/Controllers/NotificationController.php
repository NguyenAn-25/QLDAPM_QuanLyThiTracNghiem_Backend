<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNotificationRequest;
use App\Http\Requests\UpdateNotificationRequest;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Traits\ApiResponseTrait;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->notificationService->getAll();
        return $this->success($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNotificationRequest $request)
    {
        $data = $this->notificationService->add($request->validated());
        return $this->success($data, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Notification $thongbao)
    {
        $data = $this->notificationService->getOne($thongbao);
        return $this->success($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNotificationRequest $request, Notification $thongbao)
    {
        $data = $this->notificationService->update($request->validated(), $thongbao);
        return $this->success($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $thongbao)
    {
        $data = $this->notificationService->delete($thongbao);
        return $this->success($data);
    }
}
