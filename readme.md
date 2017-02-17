# Fractalfy
Laravel Wrapper for Fractal

## Usage

### Step 1: Install Through Composer

```
composer require codingphase/fractalfy
```

### Step 2: Register Service Provider
Add your new provider to the `providers` array of `config/app.php`:
```php
  'providers' => [
      // ...
      CodingPhase\Fractalfy\FractalfyServiceProvider::class,
      // ...
  ],
```

## Fractal methods
Extend your controller with FractalfyController
```php
class DashboardController extends FractalfyController
{
    ...
}
```

Return collection
```php
$users = Users::all();
return $this->fractal
    ->collection($users, new UserTransformer)
    ->get();
```

Return resource with pagination
```php
$users = Users::all();
return $this->fractal
    ->paginate($users, new UserTransformer)
    ->get();
```

## Fractalfy Helpers
Use Fractalfy Helpers (already included in FractalfyController)

Popular
```php
return $this->respondOK();
return $this->respondNotFound();
return $this->respondUnauthorized();
return $this->respondUnprocessable();
return $this->respondBadRequest();
return $this->respondWithSuccess(200); //any success code
return $this->respondWithError(400); //any success code
```

Other
```php
return $this->respondOK($message); //pass message to respond
return $this->setMessage($message)->respondOK();
return $this->setMessage($message)->setStatusCode($statuscode)->respondWithSuccess(); 
return $this->setMessage($message)->setStatusCode($statuscode)->respondWithError(); 
```



