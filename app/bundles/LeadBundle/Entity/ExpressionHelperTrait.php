<?php

namespace Mautic\LeadBundle\Entity;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Query\QueryBuilder;

trait ExpressionHelperTrait
{
    /**
     * @param $includeIsNull    true/false or null to auto determine based on operator
     *
     * @return mixed
     */
    public function generateFilterExpression(QueryBuilder|\Doctrine\ORM\QueryBuilder $q, $column, $operator, $parameter, $includeIsNull, CompositeExpression $appendTo = null)
    {
        // in/notIn for dbal will use a raw array
        if (!is_array($parameter) && !str_starts_with($parameter, ':')) {
            $parameter = ":$parameter";
        }

        if (null === $includeIsNull) {
            // Auto determine based on negate operators
            $includeIsNull = in_array($operator, ['neq', 'notLike', 'notIn']);
        }

        if ($includeIsNull) {
            $expr = $q->expr()->orX(
                $q->expr()->$operator($column, $parameter),
                $q->expr()->isNull($column)
            );
        } else {
            $expr = $q->expr()->$operator($column, $parameter);
        }

        if ($appendTo) {
            $appendTo->with($expr);

            return $appendTo;
        }

        return $expr;
    }
}
