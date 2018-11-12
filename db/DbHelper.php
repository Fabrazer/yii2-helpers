<?php
/*
 * Copyright © 2018, visoma gmbh, Fabriece Knedel <fk@visoma.de>
 * https://www.visoma.de
 * office@visoma.de
 */
namespace fabrazer\helpers\db;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;


/**
 * DbHelper
 *
 * @author Fabriece Knedel <fk@visoma.de>
 */
class DbHelper
{
    /**
     *
     * @param ActiveQuery $query
     * @param ActiveRecord $model
     * @param string $attribute
     * @return void
     */
    public static function FilterWhereMultiple(ActiveQuery &$query, ActiveRecord $model, $attribute, $operator = 'AND')
    {
		$attribute = explode('.', $attribute);

		// Get value from model
		if(static::_getAlias($query, $model) == $attribute[0]) {
			$values = ArrayHelper::getValue($model, implode('.', array_slice($attribute, 1)));
		} else {
			$_a = implode('.', $attribute);

			// Get value from model
			if($model->hasAttribute($_a)) {
				$values = $model->getAttribute($_a);
			}
			// Get value from model's relation
			else {
				$values = ArrayHelper::getValue($model, $_a);
			}
		}
		
        if(empty($values) && !is_string($values)) {
            return;
		}		

        if (!is_array($values)) {
            $values = [$values];
		}
		
		// Convert attribute for query (get the last 2 items from array)
		$attribute = implode('.', array_slice($attribute, -2));


        $condition = ['OR'];
        foreach ($values as $value) {
            $condition[] = ['LIKE', $attribute, $value];
        }

        if($operator == 'AND') {
            $query->andFilterWhere($condition);
        } else if($operator == 'OR') {
            $query->orFilterWhere($condition);
        } else {
            throw new \Exception('Invalid operator.');
        }
	}
	
	private static function _getAlias(ActiveQuery $query, ActiveRecord $model)
	{
		$from = $query->getTablesUsedInFrom();

		$alias = array_search('{{'.$model->tableName().'}}', $from);

		return str_replace(['{{', '}}'], null, $alias);
		#print_r($alias); exit;
	}
}