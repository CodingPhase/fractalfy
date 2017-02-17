<?php

namespace Hesto\Fractalfy\Traits;

use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

trait Helpers
{
    protected $statusCode = SymfonyResponse::HTTP_OK;

    protected $message = null;

    /**
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function respondNotFound($message = null)
    {
        return $this
            ->setStatusCode(SymfonyResponse::HTTP_NOT_FOUND)
            ->setMessage($message)
            ->respondWithError();
    }

    /**
     * @param null $message
     * @return mixed
     */
    public function respondUnauthorized($message = null)
    {
        return $this
            ->setStatusCode(SymfonyResponse::HTTP_UNAUTHORIZED)
            ->setMessage($message)
            ->respondWithError();
    }

    /**
     * @param null $message
     * @return mixed
     */
    public function respondUnprocessable($message = null)
    {
        return $this
            ->setStatusCode(SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY)
            ->setMessage($message)
            ->respondWithError();
    }

    /**
     * @param null $message
     * @return mixed
     */
    public function respondBadRequest($message = null)
    {
        return $this
            ->setStatusCode(SymfonyResponse::HTTP_BAD_REQUEST)
            ->setMessage($message)
            ->respondWithError();
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function respondOK($message = null)
    {
        return $this
            ->setStatusCode(SymfonyResponse::HTTP_OK)
            ->setMessage($message)
            ->respondWithSuccess();
    }

    /**
     * @return mixed
     */
    public function respondWithSuccess($statusCode = null)
    {
        if($statusCode != null) {
            $this->setStatusCode($statusCode);
        }

        if ($this->message == null) {
            $this->message = SymfonyResponse::$statusTexts[$this->getStatusCode()];
        }

        return $this->respond([
            'message'     => $this->getMessage(),
            'status_code' => $this->getStatusCode(),
        ]);
    }

    /**
     * @return mixed
     */
    public function respondWithError($statusCode = null)
    {
        if($statusCode != null) {
            $this->setStatusCode($statusCode);
        }

        if ($this->message == null) {
            $this->message = SymfonyResponse::$statusTexts[$this->getStatusCode()];
        }

        return $this->respond([
            'error' => [
                'message'     => $this->getMessage(),
                'status_code' => $this->getStatusCode(),
            ],
        ]);
    }

    /**
     * @param $data
     * @param array $headers
     * @return mixed
     */
    public function respond($data, $headers = [])
    {
        return Response::json($data, $this->getStatusCode(), $headers);
    }

}
