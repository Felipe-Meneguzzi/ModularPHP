<?php
declare(strict_types=1);

namespace App\Core\Http;

use App\Core\Exception\PaginationStackException;
use ReflectionObject;
use Illuminate\Database\Eloquent\Model;

class PaginationObject {
    public int $offset;

    private function __construct(
        public int $page,
        public int $limit,
        public string $search,
        public string $like,
        public array $sort,
        public array $eqor,
        public array $eqand,
        public array $null,
        public array $not_null
    ) {
        $this->offset = ($page - 1) * $limit;
    }

    public static function fromArray(array $params): self {
        $errors = [];


        // page
        $page = 1;
        if (isset($params['page'])) {
            if (filter_var($params['page'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) === false) {
                $errors[] = "'page' must be a positive integer";
            } else {
                $page = (int)$params['page'];
            }
        }


        // limit
        $limit = 30;
        if (isset($params['limit'])) {
            if (filter_var($params['limit'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) === false) {
                $errors[] = "'limit' must be a positive integer";
            } else {
                $limit = (int)$params['limit'];
            }
        }


        // search
        $search = '';
        if (isset($params['search'])) {
            $search = $params['search'];
        }


        // like
        $like = '';
        if (isset($params['like'])) {
            $like = $params['like'];
        }


        // sort
        $sort = [];
        if (isset($params['sort'])) {
            $sort = explode(',', $params['sort']);
        }


        // eqor
        $eqor = [];
        if (isset($params['eqor'])) {
            if (!is_array($params['eqor'])) {
                $errors[] = "'eqor' must be an associative array with string keys and values";
            } else {
                foreach ($params['eqor'] as $key => $value) {
                    $eqor[$key] = explode(',', $value);
                }
            }
        }


        // eqand
        $eqand = [];
        if (isset($params['eqand'])) {
            if (!is_array($params['eqand'])) {
                $errors[] = "'eqand' must be an associative array with string keys and values";
            } else {
                foreach ($params['eqand'] as $key => $value) {
                    $eqand[$key] = explode(',', $value);
                }
            }
        }


        // null
        $null = [];
        if (isset($params['null'])) {
            $null = explode(',', $params['null']);
        }


        // not_null
        $not_null = [];
        if (isset($params['not_null'])) {
            $not_null = explode(',', $params['not_null']);
        }








        if (!empty($errors)) {
            throw new PaginationStackException($errors, 400);
        }

        return new self(
            page: $page,
            limit: $limit,
            search: $search,
            like: $like,
            sort: $sort,
            eqor: $eqor,
            eqand: $eqand,
            null: $null,
            not_null: $not_null
        );
    }



    public function generateQueryFromPagination(Model $entity) {
        $query = $entity;

        $searchIgnore = $entity->searchIgnore ?? [];
        $columns = $query->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing($query->getTable());


        $query = $this->addSearchClause($query, $columns, $searchIgnore);

        $query = $this->addLikeClause($query, $columns, $searchIgnore);

        return $query->limit($this->limit)->offset($this->offset);
    }


    // like
    private function addLikeClause($query, $columns, $searchIgnore) {
        $like = $this->like;
        if (!empty($like)) {
            $query = $query->where(function ($q) use ($columns, $like, $searchIgnore) {
                foreach ($columns as $column) {
                    if (in_array($column, $searchIgnore)) {
                        continue;
                    }
                    $q->orWhere($column, 'like', '%' . $like . '%');
                }
            });
        }

        return $query;
    }


    // search
    private function addSearchClause($query, $columns, $searchIgnore) {
        $search = $this->search;
        if (!empty($search)) {
            $query = $query->where(function ($q) use ($columns, $search, $searchIgnore) {
                foreach ($columns as $column) {
                    if (in_array($column, $searchIgnore)) {
                        continue;
                    }
                    $q->orWhere($column, '=', $search);
                }
            });
        }

        return $query;
    }


    public function toArray(): array {
        $array = [];
        $reflection = new ReflectionObject($this);

        foreach ($reflection->getProperties() as $property) {
            if(!empty($property->getValue($this))){
                $array[$property->getName()] = $property->getValue($this);
            }
        }

        return $array;
    }

}