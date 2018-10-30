<?php
/*
 * Copyright © 2018, visoma gmbh, Fabriece Knedel <fk@visoma.de>
 * https://www.visoma.de
 * office@visoma.de
 */
namespace fabrazer\helpers\traits;

use yii\data\ActiveDataProvider;
use fabrazer\helpers\db\DbHelper;

/**
 * EnableRelatedModelSearch
 *
 * @author Fabriece Knedel <fk@visoma.de>
 * @see https://www.yiiframework.com/wiki/653/displaying-sorting-and-filtering-model-relations-on-a-gridview
 */
trait EnableRelatedModelSearch
{
	private static $related_attributes = [];
	
	/**
	 * Resolves relation
	 *
	 * @param mixed $relation
	 * @return array
	 */
	private function _resolveRelation($relation, $alias = null)
	{
		// Extract alias from array
		if(is_array($relation))
		{
			$keys = array_keys($relation);
			$alias = $keys[0];

			// Resolve relation string
			$relation = $this->_resolveRelation($relation[$alias], $alias);
		} else

		// Extract class from string (final step)
		if(is_string($relation))
		{
			$parts = explode('.', $relation);
			$class = end($parts);

			if($alias === null) {
				$alias = $class;
			}

			$relation = [
				'alias' => $alias,
				'relation' => $relation,
				'class' => $class
			];
		}
		
		return $relation;
	}

	/**
	 * Undocumented function
	 *
	 * @param array $relation
	 * @return array
	 */
    private function _getRelatedModelsAttributes(array $relation)
    {
        if(isset(self::$related_attributes[$relation['relation']])) {
            return self::$related_attributes[$relation['relation']];
        }

        $attributes = (new \ReflectionClass('\\app\\models\\'.ucfirst($relation['class'])))->newInstance()->attributes();

        foreach ($attributes as $attribute) {
            self::$related_attributes[$relation['relation']][] = $relation['relation'].'.'.$attribute;
        }

        return self::$related_attributes[$relation['relation']];
	}

	/**
	 * Undocumented function
	 *
	 * @param array $relation
	 * @param ActiveDataProvider $dataProvider
	 * @return void
	 */
    private function _enableRelatedModelSearchSorting(array $relation, ActiveDataProvider $dataProvider)
    {
        foreach ($this->_getRelatedModelsAttributes($relation) as $attribute) {
            $dataProvider->sort->attributes[$attribute] = [
                'asc' => [$attribute => SORT_ASC],
                'desc' => [$attribute => SORT_DESC],
            ];
        }
    }

	/**
	 * Undocumented function
	 *
	 * @param array $relation
	 * @param ActiveDataProvider $dataProvider
	 * @param string $operator
	 * @return void
	 */
    private function _enableRelatedModelSearchFiltering(array $relation, ActiveDataProvider $dataProvider, $operator = 'AND')
    {
        $query = $dataProvider->query;
        foreach ($this->_getRelatedModelsAttributes($relation) as $attribute) {
			#$attribute = implode('.', array_slice(explode('.', $attribute), -2));
			
			#print_r($attribute); echo '<br>'; 

            // Add filter
            DbHelper::FilterWhereMultiple($query, $this, $attribute, $operator);
        }
    }

	/**
	 * Get missing rules from relation
	 *
	 * @param array $relation
	 * @param array $rules
	 * @return void
	 */
    protected function enableRelatedModelSearchRules(array $relation, array &$rules)
    {
        foreach ($relation as $_r) {
			// Resolve relation
			$_r = $this->_resolveRelation($_r);

            $rules[] = [$this->_getRelatedModelsAttributes($_r), 'safe'];
        }
    }

	/**
	 * Get missing attributes from relation
	 *
	 * @param array $relation
	 * @param array $attributes
	 * @return array
	 */
    protected function enableRelatedModelSearchAttributes(array $relation, array $attributes)
    {
		foreach ($relation as $_r)
		{
			// Resolve relation
			$_r = $this->_resolveRelation($_r);

            $attributes = array_merge($attributes, $this->_getRelatedModelsAttributes($_r));
		}

        return $attributes;
	}
	
	/**
	 * Undocumented function
	 *
	 * @param array $relation
	 * @param ActiveDataProvider $dataProvider
	 * @param string $operator
	 * @return void
	 */
	protected function enableRelatedModelSearch(array $relation, ActiveDataProvider $dataProvider, $operator = 'AND')
    {
		foreach ($relation as $_r)
		{
			// Resolve relation
			$_r = $this->_resolveRelation($_r);

            $query = $dataProvider->query;
            $query->joinWith([$_r['relation'].' as '.$_r['alias']]);

            $this->_enableRelatedModelSearchSorting($_r, $dataProvider);
            $this->_enableRelatedModelSearchFiltering($_r, $dataProvider, $operator);
		}
    }
}