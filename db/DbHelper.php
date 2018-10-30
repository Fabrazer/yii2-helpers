<?php
/*
 * Copyright © 2018, visoma gmbh, Fabriece Knedel <fk@visoma.de>
 * https://www.visoma.de
 * office@visoma.de
 */
namespace fabrazer\helpers\db;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;


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
		
        $values = $model->getAttribute(implode('.', $attribute));

        if(empty($values) && strpos($model->tableName(), $attribute[0]) !== false) {
            $values = $model->getAttribute($attribute[1]);
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
}