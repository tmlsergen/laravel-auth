<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ServiceException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\UpdateRequest;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly UserService $userService)
    {
    }

    #[Response([
        'status' => 'success',
        'message' => 'User data retrieved successfully',
        'data' => [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@doe.com',
        ],
    ])]
    public function me(Request $request): \Illuminate\Http\JsonResponse
    {
        $claim = $request->attributes->get('claim');

        return $this->successResponse([
            'id' => $claim->data->data->user->id,
            'name' => $claim->data->data->user->name,
            'email' => $claim->data->data->user->email,
        ], 'User data retrieved successfully');
    }

    #[BodyParam('name', 'string', 'The name of the user.', example: 'John Doe')]
    #[Response('', 204)]
    public function update(UpdateRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $claim = $request->attributes->get('claim');

        try {
            $user = $this->userService->getUserById($claim->data->data->user->id);

            $this->userService->updateProfile($validated, $user->id);

            return $this->successResponse([], 'User profile updated successfully', 204);
        } catch (ServiceException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
