<?php

namespace Igniter\Flame\Traits\NestedSet;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Kalnoy\Nestedset\NodeTrait;

trait NestedTree
{
    use NodeTrait {
        NodeTrait::create as parentCreate;
    }

    /**
     * Get the lft key name.
     *
     * @return  string
     */
    public function getLftName()
    {
        return defined('static::NEST_LEFT') ? static::NEST_LEFT : 'nest_left';
    }

    /**
     * Get the rgt key name.
     *
     * @return  string
     */
    public function getRgtName()
    {
        return defined('static::NEST_RIGHT') ? static::NEST_RIGHT : 'nest_right';
    }

    /**
     * Get the parent id key name.
     *
     * @return  string
     */
    public function getParentIdName()
    {
        return defined('static::PARENT_ID') ? static::PARENT_ID : 'parent_id';
    }

    public static function create(array $attributes = [], $sessionKey = null)
    {
        $children = array_pull($attributes, 'children');

        $instance = new static($attributes);

        $parent = $instance->getParentId();
        if ($parent instanceof self) {
            $instance->appendToNode($sessionKey);
        }

        $instance->save(null, $parent);

        // Now create children
        $relation = new EloquentCollection;

        foreach ((array)$children as $child) {
            $relation->add($child = static::create($child, $instance));

            $child->setRelation('parent', $instance);
        }

        $instance->refreshNode();

        return $instance->setRelation('children', $relation);
    }

    /**
     * {@inheritdoc}
     *
     * @since 2.0
     */
    public function newEloquentBuilder($query)
    {
        return new QueryBuilder($query);
//        return new Builder($query);
    }
}