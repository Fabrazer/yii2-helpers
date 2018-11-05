# yii2-helpers

## Features

1. **Displaying, Sorting and Filtering Model Relations on a GridView**

    The **EnableRelatedModelSearch** trait is used to easily enable the displaying, sorting and filtering via model relations on a GridView.
    
    This trait does the job which is described under this link: https://www.yiiframework.com/wiki/653/displaying-sorting-and-filtering-model-relations-on-a-gridview

## Installation

```
$ php composer.phar require fabrazer/yii2-helpers "@dev"
```

## Usage

1. **EnableRelatedModelSearch**

    1. Add missing rules to your search model
    
          ```
          public function rules()
          {
            $rules = [
              // Your rules
            ];

            // Add rules of the model relations "city" and "country"
            $this->enableRelatedModelSearchRules(['city', 'country'], $rules);

            return $rules;
          }
          ```
        
      2. Add missing attributes
      
          ```
          public function attributes()
          {
            // Add attributes of the model relations "city" and "country"
            return $this->enableRelatedModelSearchAttributes(['city', 'country'], parent::attributes());
          }
          ```
        
      3. Add sorting and filtering to the query
      
          ```
          public function search($params)
          {
            // Your code ...

            // Add sorting and filtering to the query
            $this->enableRelatedModelSearch(['city', 'country'], $dataProvider);
          }
          ```
          