<?php

namespace Bigfork\SilverstripeFormCapture\Filters;

use SilverStripe\ORM\DataQuery;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\Filters\PartialMatchFilter;

/**
 * Near-clone of PartialMatchFilter, except this forces the use of HAVING instead of WHERE. Used to filter on
 * aggregate columns where the ORM doesn't correctly detect them as aggregate
 */
class HavingPartialMatchFilter extends PartialMatchFilter
{
    protected function applyOne(DataQuery $query): DataQuery
    {
        $this->model = $query->applyRelation($this->relation);
        $comparisonClause = DB::get_conn()->comparisonClause(
            $this->getDbName(),
            null,
            false, // exact?
            false, // negate?
            $this->getCaseSensitive(),
            true
        );

        $clause = [$comparisonClause => $this->getMatchPattern($this->getValue())];

        return $this->aggregate ?
            $this->applyAggregate($query, $clause) :
            $query->having($clause);
    }

    protected function applyMany(DataQuery $query): DataQuery
    {
        $this->model = $query->applyRelation($this->relation);
        $whereClause = [];
        $comparisonClause = DB::get_conn()->comparisonClause(
            $this->getDbName(),
            null,
            false, // exact?
            false, // negate?
            $this->getCaseSensitive(),
            true
        );
        foreach ($this->getValue() as $value) {
            $whereClause[] = [$comparisonClause => $this->getMatchPattern($value)];
        }
        return $query->having($whereClause);
    }

    protected function excludeOne(DataQuery $query): DataQuery
    {
        $this->model = $query->applyRelation($this->relation);
        $comparisonClause = DB::get_conn()->comparisonClause(
            $this->getDbName(),
            null,
            false, // exact?
            true, // negate?
            $this->getCaseSensitive(),
            true
        );
        return $query->having([
            $comparisonClause => $this->getMatchPattern($this->getValue())
        ]);
    }

    protected function excludeMany(DataQuery $query): DataQuery
    {
        $this->model = $query->applyRelation($this->relation);
        $values = $this->getValue();
        $comparisonClause = DB::get_conn()->comparisonClause(
            $this->getDbName(),
            null,
            false, // exact?
            true, // negate?
            $this->getCaseSensitive(),
            true
        );
        $parameters = [];
        foreach ($values as $value) {
            $parameters[] = $this->getMatchPattern($value);
        }
        // Since query connective is ambiguous, use AND explicitly here
        $count = count($values ?? []);
        $predicate = implode(' AND ', array_fill(0, $count ?? 0, $comparisonClause));
        return $query->having([$predicate => $parameters]);
    }
}
