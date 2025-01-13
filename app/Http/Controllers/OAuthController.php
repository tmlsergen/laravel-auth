<?php

namespace App\Http\Controllers;

use App\Exceptions\ServiceException;
use App\Http\Requests\Web\OAuth\AuthorizeRequest;
use App\Http\Requests\Web\OAuth\IndexRequest;
use App\Http\Requests\Web\OAuth\TokenRequest;
use App\Services\OAuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Random\RandomException;

class OAuthController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly OAuthService $oauthService)
    {
    }

    public function index(IndexRequest $request): \Illuminate\Contracts\View\View
    {
        $validated = $request->validated();

        try {
            $parameters = $this->oauthService->generateCredentials($validated);

            return view('oauth.authorize', $parameters);
        } catch (ServiceException $e) {
            return view('oauth.error', ['error' => $e->getMessage()]);
        }
    }

    public function authorize(AuthorizeRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            if (! $validated['approve']) {
                $redirect = $validated['redirect_uri'].'?error=access_denied';

                return redirect()->to($redirect)->header('Location', $validated['redirect_uri'].'?error=access_denied');
            }

            $redirect = $this->oauthService->generateCode($validated);

            return redirect()->to($redirect)->header('Location', $redirect)->setStatusCode(302);
        } catch (ServiceException $e) {
            return redirect()->to($validated['redirect_uri'].'?error='.$e->getMessage());
        } catch (RandomException $e) {
            Log::error($e->getMessage());

            return redirect()->to($validated['redirect_uri'].'?error=server_error');
        }
    }

    public function token(TokenRequest $request): JsonResponse
    {
        $validated = $request->validated();
        if (isset($validated['code'])) {
            $validated['code'] = urldecode($validated['code']);
        }

        try {
            $tokenResult = $this->oauthService->generateToken($validated);

            return $this->successResponse($tokenResult);
        } catch (ServiceException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function revoke(Request $request): JsonResponse
    {
        /** @var \stdClass $claim */
        $claim = $request->attributes->get('claim');
        try {
            $this->oauthService->revokeToken($claim);

            return $this->successResponse([], 'Token revoked');
        } catch (ServiceException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
