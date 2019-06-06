### Code Refactor
***
 - Validation must be applied to any function that is passing data to other function, specially in case of database 
 queries.
 
 - variable names should be `camelCase`(PSR-2)
 
 - There should be no keys validation inside `Repository`. All checking must be validated using `Validator` in 
 `Controller`.
 
 - Method docs must contain what method does.
 
 - There should be no database interaction from Controller.
 
 - Single quotes `''` must be used instead of double quotes `""`
 
 - Bind interface of `Repository` via `ServiceProvider`. Don't use `RepositoryClass` directly. It provides more control 
 over structure.
 
 - Try to omit variables that are not in use(free up memory).
 
 - I did some code refactoring via `PSR-2` standards to these files.
 
 - use of proper return type instead of `mixed` in function docs. It gives more insights what one will get from the 
 method.
 
 - Maintain a config file with defaults, load the config key and make changes to it. it will save a lot of else 
 conditions. Example:
 ```
   if (isset($data['customer_phone_type'])) {
     $data['customer_phone_type'] = 'yes';
   } else {
     $data['customer_phone_type'] = 'no';
   }
 ```
 instead of this, load config keys and use those.
 ```
    $data = config->get('app.defaultOptions');
     if (isset($data['customer_phone_type'])) {
       $data['customer_phone_type'] = 'yes';
     }
 ```
 
 Default options for `customer_phone_type` can have `no` as a default. This will omit a lot of else conditions from a 
 single file, and from entire system.
 
 - Instead of returning everything with `response` method, use simple `return` without `response`. Laravel will wrap it 
 into `response` by itself. However, use `response` method if you want to send back specific `Status Code` and 
 `headers`.
 

 ---
 - `_app` contains old code, while `app` contains refactored code.
 
