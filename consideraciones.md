
app/Http/Controllers/Api/AuthController.php
Outdated
Comment on lines 15 to 17
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
@jenriquez-bit
jenriquez-bit
on Sep 25
Añade las validaciones por medio de un form request. Para que sea más sencillo el controller

https://laravel.com/docs/12.x/validation#form-request-validation


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email requerido'
            ], 400);
        }
@jenriquez-bit
jenriquez-bit
on Sep 25
Si utilizas el form request, no es necesario enviar una respuesta en caso de fallo. Todo lo hace el mismo request

if (app()->environment('production')) {
            \Mail::to($email)->send(new \App\Mail\OtpMail($otp->code, $user->name));
        } else {
            // Log del código en desarrollo
            \Log::info("Código OTP para $email: " . $otp->code);
        }
@jenriquez-bit
jenriquez-bit
on Sep 25
No es necesario hacer esta distinción, en laravel hay un driver de log para el mail, por lo que solo es configurar el driver en el .env


Outdated
        ]);
    }

    public function verifyOtp(Request $request)
@jenriquez-bit
jenriquez-bit
on Sep 25
Para llevar beunas prácticas, es recomendable que las operaciones que no entren dentro de un CRUD se utilicen controllers invokables

https://laravel.com/docs/12.x/controllers#single-action-controllers


 'reports' => $reports->map(function ($report) {
                return [
                    'id' => $report->id,
                    'name' => $report->name,
                    'report' => $report->report,
                    'created_at' => $report->created_at,
                ];
            })
@jenriquez-bit
jenriquez-bit
on Sep 25
En lugar de utilizar este tipo de acercamientos, mejor utiliza API Resources

https://laravel.com/docs/12.x/eloquent-resources

return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'account_id' => $user->account_id,
                'base_url' => $user->base_url,
                'color_primary' => $user->color_primary,
                'image' => $user->image,
                'token' => $user->token,
                'reports' => $user->reports,
            ],
            'access_token' => $token
        ]);
@jenriquez-bit
jenriquez-bit
on Sep 25
En las respuestas no es necesario regresar el success, ya que si regresas un http code 200, se da por hecho que fue correcto

Cualquier error regresaría un código diferente

Te recomiendo que utilices api reources

https://laravel.com/docs/12.x/eloquent-resources

 self::where('email', $email)
            ->where('expires_at', '<', now())
            ->delete();
@jenriquez-bit
jenriquez-bit
on Sep 25
Si la función del método es generar un código nuevo, no debe de realizar el eliminado. Eso debería de estar en otro lado

Te recomiendo que se haga un task para eliminar todos los expirados que se ejecute cada día


   $table->string('email');
            $table->string('code', 6);
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
@jenriquez-bit
jenriquez-bit
on Sep 25
En este tipo de campos, lo recomendable es que no sea de tipo boolean, sino que sea de tipo datetime

De esta forma, cuand es null sabes que no se ha utilizado, y cuando se utiliza, sabes cuando fue la fecha y hora que se utilizó

Utiliza el campo con nombre used_at

  Route::get('/reports', [ReportController::class, 'index']);
    Route::get('/reports/{id}', [ReportController::class, 'show']);
@jenriquez-bit
jenriquez-bit
on Sep 25
Al ser las rutas para un crud, utiliza mejor

Route::apiResource('reports')->only(['index', 'show']):
De esta forma te genera un nombre automáticamente a la ruta

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
@jenriquez-bit
jenriquez-bit
on Sep 25
En el controller no tienes un método de logout

 ->where('used', false)
                  ->first();

        if (!$otp || $otp->isExpired()) {
@jenriquez-bit
jenriquez-bit
on Sep 25
Además de mandar el error, debes tener un rate limit. Tomando en cuenta que son solo 6 dígitos, sería muy fácil adivinar el código.

https://laravel.com/docs/12.x/rate-limiting#basic-usage

 public static function cleanExpired()
    {
        return self::where('expires_at', '<', now())->delete();
    }
@jenriquez-bit
jenriquez-bit
on Oct 9
Para hacer esta limpieza de código expirados, laravel ya cuenta out of the box con un sistema para hacer esto precisamente. Te recomiendo utilizarlo mejor.

Puedes revisarlo en: https://laravel.com/docs/12.x/eloquent#pruning-models

return [
            'email' => 'required|email',
        ];
@jenriquez-bit
jenriquez-bit
on Oct 9
Esto es opinión mia, pero prefiero que las reglas de validación estén en formato de array a que estén en un string. Se lee mejor y no está todo amontonado en un string.

Quedaría así:

return [
            'email' => ['required', 'email'],



            static::creating(function ($model) {
            if (empty($model->account_id)) $model->account_id = '';
            if (empty($model->base_url)) $model->base_url = '';
            if (empty($model->color_primary)) $model->color_primary = '#0087FA';
            if (empty($model->image)) $model->image = '';
            if (empty($model->token)) $model->token = '';
            if (empty($model->reports)) $model->reports = [];
        });
@jenriquez-bit
jenriquez-bit
on Oct 9
En lugar de utilizar esta función podrías utilizar la funcionalidad de eloquent para inicializar attributes:

https://laravel.com/docs/12.x/eloquent#default-attribute-values


{
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('id')->primary();
@jenriquez-bit
jenriquez-bit
on Oct 9
Por qué estás utilizando uuid en lugar de enteros como llaves primarias?.

Hacer esto se vuelve más complejo a a larga para las relaciones entre modelos. TRe recomiendo a que lo dejes como entero




  return response()->json([
            'message' => 'Logout exitoso'
        ]);
@jenriquez-bit
jenriquez-bit
on Oct 9
Te recomiendo que en casos comop estos donde no tienes nada que devolver, mejor regreses un 204 indicando que todo fue correcto, pero no hay contenido en la respuesta



if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }
@jenriquez-bit
jenriquez-bit
on Oct 9
No es recomendable regresar mensajes de que el correo no existe, ya que puede servir para atacantes para enumerar correos

Siempre devuelve un código http exitoso y en el cliente se maneja el mensaje de retroalimentación. Si el correo existe te llegará uin correo con un código


 public function show(Request $request, $id)
    {
        $report = Report::where('user_id', $request->user()->id)->find($id);

        if (!$report) {
            return response()->json([
                'message' => 'Reporte no encontrado'
            ], 404);
        }

        return new ReportResource($report);
    }
@jenriquez-bit
jenriquez-bit
on Oct 9
Aquí puedes utilizar una funcionalidad de laravel que se llama Model binding, con el cual puedes obtener el modelo directamente del container de laravel.

https://laravel.com/docs/12.x/routing#route-model-binding

Quedaría así:

public function show(Request $request, Report $report)
Y ya solo tendrías que validar que el reporte pertenezca al usuario



  if ($request->user()->id !== $id) {
            return response()->json([
                'message' => 'No autorizado'
            ], 403);
        }
@jenriquez-bit
jenriquez-bit
on Oct 9
Para no hacer las validaciones manualmente, te recomiendo que utilices las políticas de laravel.

https://laravel.com/docs/12.x/authorization#creating-policies

Es más seguro y sencillo manejarlos así



  }

        $user = $request->user();
        $user->update($request->only(['name', 'color_primary', 'image']));
@jenriquez-bit
jenriquez-bit
on Oct 9
Cuando realizas una actualización o inserción así, es mejor utilizar los campos que se validaron.

En tu método puedes cambiarlo así:

user->update($request->validated());


 return response()->json([
            'message' => 'Usuario actualizado',
            'user' => new UserResource($user)
        ]);
@jenriquez-bit
jenriquez-bit
on Oct 9
No es necesario que regreses un mensaje, solo regresa el modelo

return UserResource::make($user)->response();




class VerifyOtpController extends Controller
{
    public function __invoke(VerifyOtpRequest $request)
    {
        $otp = Otp::where('email', $request->email)
                  ->where('code', $request->code)
                  ->whereNull('used_at')
                  ->first();

        if (!$otp || $otp->isExpired()) {
            return response()->json([
                'message' => 'Código inválido o expirado'
            ], 400);
        }

        $otp->update(['used_at' => now()]);
        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'user' => new UserResource($user),
            'access_token' => $token
        ]);
    }
} No newline at end of file
@jenriquez-bit
jenriquez-bit
on Oct 9
Lo que te comentaba del rate limit es mejor ponerlo desde aquí, más que como un global.

Lo importante es que si hay fallos vayas sumando en el rate limit, hasta que llegue a un límite y de ahí regresas un código 429

Por ejemplo el token está expirado o no se encuentra añade un hit al rate limit

Normalmente en laravel se maneja así

RateLimiter::hit($this->throttleKey());
public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($request->input('email')).'|'.$request>ip());
    }
Y validas que no esté en el límite con:

if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return ...
        }
Ya cuando es exitoso el otp, puedes eliminar el rate limit con

RateLimiter::clear($this->throttleKey());


    public function user()
    {
        return $this->belongsTo(User::class);
    }
@jenriquez-bit
jenriquez-bit
on Oct 9
Siempre en todas las funciones, argumentos y atributos de clase añade el tipado.

En este caso sería así:

public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }