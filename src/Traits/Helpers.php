<?php

namespace CodingPhase\Fractalfy\Traits;

use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

trait Helpers
{
    protected $responseStatusCode = SymfonyResponse::HTTP_OK;

    protected $responseMessage = null;

    /**
     * @return integer
     */
    public function getResponseStatusCode()
    {
        return $this->responseStatusCode;
    }

    /**
     * @param int $responseStatusCode
     * @return $this
     */
    public function setResponseStatusCode($responseStatusCode)
    {
        $this->responseStatusCode = $responseStatusCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    /**
     * @param $responseMessage
     * @return $this
     */
    public function setResponseMessage($responseMessage)
    {
        $this->responseMessage = $responseMessage;

        return $this;
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function respondNotFound($message = null)
    {
        return $this
            ->setResponseStatusCode(SymfonyResponse::HTTP_NOT_FOUND)
            ->setResponseMessage($message)
            ->respondWithError();
    }

    /**
     * @param null $message
     * @return mixed
     */
    public function respondUnauthorized($message = null)
    {
        return $this
            ->setResponseStatusCode(SymfonyResponse::HTTP_UNAUTHORIZED)
            ->setResponseMessage($message)
            ->respondWithError();
    }

    /**
     * @param null $message
     * @return mixed
     */
    public function respondUnprocessable($message = null)
    {
        return $this
            ->setResponseStatusCode(SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY)
            ->setResponseMessage($message)
            ->respondWithError();
    }

    /**
     * @param null $message
     * @return mixed
     */
    public function respondBadRequest($message = null)
    {
        return $this
            ->setResponseStatusCode(SymfonyResponse::HTTP_BAD_REQUEST)
            ->setResponseMessage($message)
            ->respondWithError();
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function respondOK($message = null)
    {
        return $this
            ->setResponseStatusCode(SymfonyResponse::HTTP_OK)
            ->setResponseMessage($message)
            ->respondWithSuccess();
    }

    /**
     * @return mixed
     */
    public function respondWithSuccess($statusCode = null)
    {
        if($statusCode != null) {
            $this->setResponseStatusCode($statusCode);
        }

        if ($this->responseMessage == null) {
            $this->responseMessage = SymfonyResponse::$statusTexts[$this->getResponseStatusCode()];
        }

        return $this->respond([
            'message'     => $this->getResponseMessage(),
            'status_code' => $this->getResponseStatusCode(),
        ]);
    }

    /**
     * @return mixed
     */
    public function respondWithError($statusCode = null)
    {
        if($statusCode != null) {
            $this->setResponseStatusCode($statusCode);
        }

        if ($this->responseMessage == null) {
            $this->responseMessage = SymfonyResponse::$statusTexts[$this->getResponseStatusCode()];
        }

        return $this->respond([
            'error' => [
                'message'     => $this->getResponseMessage(),
                'status_code' => $this->getResponseStatusCode(),
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
        return Response::json($data, $this->getResponseStatusCode(), $headers);
    }

}
