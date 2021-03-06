<?php
namespace CodingPhase\Fractalfy;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Serializer\JsonApiSerializer;
use Traversable;
use JsonSerializable;
use League\Fractal\Manager;
use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Serializer\SerializerAbstract;
use CodingPhase\Fractalfy\Exceptions\InvalidTransformation;
use CodingPhase\Fractalfy\Exceptions\NoTransformerSpecified;

class Fractalfy implements JsonSerializable
{

    protected $manager;

    protected $serializer;

    protected $transformer;

    protected $paginator;

    protected $cursor;

    protected $includes = [];

    protected $excludes = [];

    protected $dataType;

    protected $data;

    protected $resourceName;

    protected $meta = [];

    /**
     * @param null|mixed $data
     * @param null|callable|\League\Fractal\TransformerAbstract $transformer
     * @param null|\League\Fractal\Serializer\SerializerAbstract $serializer
     *
     * @return \CodingPhase\Fractalfy\Fractalfy
     */
    public static function create($data = null, $transformer = null, $serializer = null)
    {
        $instance = new static(new Manager());
        $instance->data = $data ?: null;
        $instance->dataType = $data ? $instance->determineDataType($data) : null;
        $instance->transformer = $transformer ?: null;
        $instance->serializer = $serializer ?: null;

        return $instance;
    }

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Set the collection data that must be transformed.
     *
     * @param mixed $data
     * @param \League\Fractal\TransformerAbstract|callable|null $transformer
     * @param string|null $resourceName
     *
     * @return $this
     */
    public function collection($data, $transformer = null, $resourceName = null)
    {
        $this->resourceName = $resourceName;

        return $this->data('collection', $data, $transformer);
    }

    /**
     * Set the item data that must be transformed.
     *
     * @param mixed $data
     * @param \League\Fractal\TransformerAbstract|callable|null $transformer
     * @param string|null $resourceName
     *
     * @return $this
     */
    public function item($data, $transformer = null, $resourceName = null)
    {
        $this->resourceName = $resourceName;

        return $this->data('item', $data, $transformer);
    }

    /**
     * Set the data that must be transformed.
     *
     * @param string $dataType
     * @param mixed $data
     * @param \League\Fractal\TransformerAbstract|callable|null $transformer
     *
     * @return $this
     */
    public function data($dataType, $data, $transformer = null)
    {
        $this->dataType = $dataType;
        $this->data = $data;
        if (! is_null($transformer)) {
            $this->transformer = $transformer;
        }

        return $this;
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    protected function determineDataType($data)
    {
        if (is_array($data)) {
            return 'collection';
        }
        if ($data instanceof Traversable) {
            return 'collection';
        }

        return 'item';
    }

    /**
     * Set the class or function that will perform the transform.
     *
     * @param \League\Fractal\TransformerAbstract|callable $transformer
     *
     * @return $this
     */
    public function transformWith($transformer)
    {
        if (is_string($transformer) && class_exists($transformer)) {
            $transformer = new $transformer;
        }
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * Set the serializer to be used.
     *
     * @param \League\Fractal\Serializer\SerializerAbstract $serializer
     *
     * @return $this
     */
    public function serializeWith(SerializerAbstract $serializer)
    {
        $this->serializer = $serializer;

        return $this;
    }

    public function paginate(LengthAwarePaginator $paginator, $transformer = null, $resourceName = null)
    {
        $this->resourceName = $resourceName;

        return $this->collection($paginator->getCollection(), $transformer)
            ->paginateWith(new IlluminatePaginatorAdapter($paginator));
    }

    /**
     * Set a Fractal paginator for the data.
     *
     * @param \League\Fractal\Pagination\PaginatorInterface $paginator
     *
     * @return $this
     */
    public function paginateWith(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * Set a Fractal cursor for the data.
     *
     * @param \League\Fractal\Pagination\CursorInterface $cursor
     *
     * @return $this
     */
    public function withCursor(CursorInterface $cursor)
    {
        $this->cursor = $cursor;

        return $this;
    }

    /**
     * Specify the includes.
     *
     * @param array|string $includes Array or string of resources to include.
     *
     * @return $this
     */
    public function parseIncludes($includes)
    {
        $includes = $this->normalizeIncludesOrExcludes($includes);
        $this->includes = array_merge($this->includes, (array)$includes);

        return $this;
    }

    /**
     * Specify the excludes.
     *
     * @param array|string $excludes Array or string of resources to exclude.
     * @return $this
     */
    public function parseExcludes($excludes)
    {
        $excludes = $this->normalizeIncludesOrExcludes($excludes);
        $this->excludes = array_merge($this->excludes, (array)$excludes);

        return $this;
    }

    /**
     * Normalize the includes an excludes.
     *
     * @param array|string $includesOrExcludes
     *
     * @return array|string
     */
    protected function normalizeIncludesOrExcludes($includesOrExcludes = '')
    {
        if (! is_string($includesOrExcludes)) {
            return $includesOrExcludes;
        }

        return array_map(function ($value) {
            return trim($value);
        }, explode(',', $includesOrExcludes));
    }

    /**
     * Set the meta data.
     *
     * @param $array,...
     *
     * @return $this
     */
    public function addMeta()
    {
        foreach (func_get_args() as $meta) {
            if (is_array($meta)) {
                $this->meta += $meta;
            }
        }

        return $this;
    }

    /**
     * Set the resource name, to replace 'data' as the root of the collection or item.
     *
     * @param string $resourceName
     *
     * @return $this
     */
    public function withResourceName($resourceName)
    {
        $this->resourceName = $resourceName;

        return $this;
    }

    /**
     * Perform the transformation to json.
     *
     * @return string
     */
    public function toJson()
    {
        return $this->createData()->toJson();
    }

    /**
     * Perform the transformation to array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->createData()->toArray();
    }

    /**
     * Perform the transformation to array.
     *
     * @return \Illuminate\Http\Response
     */
    public function get()
    {
        return Response::json($this->createData()->toArray());
    }

    /**
     * Create fractal data.
     *
     * @return \League\Fractal\Scope
     * @throws NoTransformerSpecified
     */
    public function createData()
    {
        if (is_null($this->transformer)) {
            throw new NoTransformerSpecified();
        }
        if (! is_null($this->serializer)) {
            $this->manager->setSerializer($this->serializer);
        }
        if (! is_null($this->includes)) {
            $this->manager->parseIncludes($this->includes);
        }
        if (! is_null($this->excludes)) {
            $this->manager->parseExcludes($this->excludes);
        }

        return $this->manager->createData($this->getResource());
    }

    /**
     * Get the resource.
     *
     * @return mixed
     * @throws InvalidTransformation
     */
    public function getResource()
    {
        $resourceClass = 'League\\Fractal\\Resource\\' . ucfirst($this->dataType);
        if (! class_exists($resourceClass)) {
            throw new InvalidTransformation();
        }
        $resource = new $resourceClass($this->data, $this->transformer, $this->resourceName);
        $resource->setMeta($this->meta);
        if (! is_null($this->paginator)) {
            $resource->setPaginator($this->paginator);
        }
        if (! is_null($this->cursor)) {
            $resource->setCursor($this->cursor);
        }

        return $resource;
    }

    /**
     * Convert the object into something JSON serializable.
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Support for magic methods to included data.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return $this
     */
    public function __call($name, array $arguments)
    {
        if ($this->startsWith($name, ['include'])) {
            $includeName = lcfirst(substr($name, strlen('include')));

            return $this->parseIncludes($includeName);
        }
        if ($this->startsWith($name, ['exclude'])) {
            $excludeName = lcfirst(substr($name, strlen('exclude')));

            return $this->parseExcludes($excludeName);
        }
        trigger_error('Call to undefined method ' . __CLASS__ . '::' . $name . '()', E_USER_ERROR);
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string $haystack
     * @param  string|array $needles
     * @return bool
     */
    protected function startsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && substr($haystack, 0, strlen($needle)) === (string)$needle) {
                return true;
            }
        }

        return false;
    }
}