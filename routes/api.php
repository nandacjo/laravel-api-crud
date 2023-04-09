 <?php

    use App\Http\Controllers\Api\UserController;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Route;
    use Whoops\Run;

    /*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

    // Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    //     return $request->user();
    // });

    Route::get('/tests', function () {
        p('tesss');
    });
    Route::post('user/store', [UserController::class, 'store']);
    Route::get('users/get/{flag}', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::delete('user/delete/{id}', [UserController::class, 'destroy']);
    Route::put('user/update/{id}', [UserController::class, 'update']);
    Route::patch('change-password', [UserController::class, 'changePassword']);