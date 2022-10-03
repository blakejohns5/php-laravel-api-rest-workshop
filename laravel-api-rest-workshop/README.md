# First test with Laravel, follow api-rest-workshop #

This CRUD setup guide was created working along with this workshop at Assembler Institute of Technology:

https://github.com/assembler-institute/php-laravel-api-rest-workshop 


## Getting Started / Useful Commands: ##
<br>


- To create your laravel project, note you may need to uncomment [extension=fileinfo] in php.
- This practice was done in Windows 11.
<br><br>

### Creating a Project: ###
```
composer create-project laravel/laravel [project-name]
```

### Generating an app key in .env ###
```
php artisan key:generate
```

### Changing DB to sqlite ###
  - add db.sqlite file to database folder
  - add path to .env of info for server db

### Changing App name ###
  - change in .env
  ```
  APP_NAME=rest-api-laravel
  ```

<br>

# **Basic Processes** #
<br>

## Migrations, Tables, and the Database: ##
<br>

### Creating a Table in Migrations: ###
- Naming convention: create_name(plural)_type (all lower-case)
```
php artisan make:migration [migration_model_name / create_categories_model]
...
php artisan make:migration create_categories_table
```
<br>


### Working with Schema in migration tables ###

- to associate one table with another category, you can use foreignId(), passing the 'name-of-other-table-in-singular', followed by '_id'. Laravel automatically associates them.
<span style="color: lightgreen">This worked for me.</span> 
```
$table->foreignId('category_id')
```
- This is so common that you can pass the class to foreignIdFor(), for example 'Category::class', and since the table is called categories, Laravel will associate them (see Product table). <span style="color: yellow">However, this method did not work for me, and did not create the product table.</span>

```
$table->foreignIdFor(Category::class)
```
<br>

### Moving Migrations / Tables to Database: ###
** Note this will migrate all migrations to the set DB
- You may need to uncomment [extension=pdo_sqlite] in php.ini
- To view in VSCode, you also need to install sqlite viewer as extension  
```
php artisan migrate
```

- if you make a mistake with a migration command, you can use migrate:rollback, which will call the down() function in the table, dropping that table from the db
```
php artisan migrate:rollback
   INFO  Rolling back migrations.
  2022_09_28_201653_create_products_table ................................................................................................. 7ms DONE
```
- migrate:fresh will remove all tables and create then fresh in the db.
```
php artisan migrate:fresh
```
<br>

### Making models ###
- Naming convention: model in Capital letters and plural, and table in singular, eg: 
  ``` 
  php artisan make:model Category
  ```
- if you create a model without the table, you can use the -m parameter, it creates the model and automatically adds a migration for the table 
  ```
  php artisan make:model Product -m 
  ```
<br>

## Views: ##

- Routes created in routes/web.php acces their view from blade.php files with the same name, in the resources/views. See BLADE TEMPLATES in official documentation


## Controllers ##

- use make:contoller
- Naming convention: CamelCase
- You can add parameters, for example --api (to keep Laravel from adding the methods for corresponding view, since we're only working on an api in this case), -m [ModelName] to connect it to the model
```
php artisan make:controller CategoriesController --api -m Category
```
- This will create within the controller the methods for:
  - returning a list of the table resourcess (categories, in this case)
  - creating new resource for the table (another category)
  - displaying one of the table resouress (a category)
  - updating one of the resourcess (a category)
  - eliminating one of the resources (a category)

<br>

## **CRUD: CREATE Resources through the API** ## 
### from Controller, method store() ###
<br>

## Connecting Routes to Controllers ##
<br>

### General structure for functions in Controllers ###

The methods are automatically outlined in the Controllers, once created. 
The general structure is:
```
   public function <method-name>(<arguments for specific method>)
    {
        $variables = <methods to set variables>
      
        return response()->json([
            <code for return value in json format>
        ]);
    }
```
<br>

### Use routes to connect a route to your controller: ###

In this case, we want to create a new resource (or category): 
  - specify the endpoint and an extraction (something you call, it could be a function or an array containing a class and a method from the controller)
  - passing a function might look like:
  ```
  Route::post('/categories', function () {
    ...your code...
  });
  ```
  - passing an array with a class and method:
  ```
  Route::post('/categories', [CategoriesController::class, 'store']);
  ```
<br>

To see what arrives in the request, add 'dd($request)' to the Controller:
```
  public function store(Request $request)
    {
        dd($request);
    }
```

Then, when testing post to localhost:8000/categories in Postman / Insomnia, you should receive a 404 for additional paths, but for this a response.<br>
** Note: At first, received an error:<br>
<span style="color: pink">'illuminate contracts container bindingresolutionexception target class CategoriesController does not exist'</span>

<span style="color: lightgreen"><i>Solution was to import the Controller in route api.php file, with backslash instead of forward slash:</i></span>
```
use App\Http\Controllers\CategoriesController;
```
<br>
If the POST body carries JSON data, we can use the controller to get this data. Laravel automatically detects JSON.
We can also use it to create the new category in the db.
POST data example:

```
  {
    "name": "Categoría 1"
  }
```

Controller 'store' method:
```
  public function store(Request $request)
    {
        $category = new Category();
        $category->name = $request->name;
        $category->save();

         // return 'well done!';
        return response()->json([
            'category' => $category
        ]);
    }
```
<br>

### Validate data POSTed ### 
<br>

The above works, but since we originally set model for name in categories as unique(), we'll get an error if we post the same name.
We can use validation in the controller. The Request class used in the controller has a validate method, that takes an array with validation rules.<br>
We pass the name of the field (in this case 'name', and the rules separated by the '|' character):
1. required 
2. string 
3. max 80 chars 
4. unique in the categories table, the 'name' field
```
        $validated = $request->validate([
            'name' => 'required|string|max:80|unique:categories,name',  // unique in the model categories name
        ]);
        $category = new Category();
        $category->name = $request->name;
        ...
```
The response message from Postman will show the validation error, if not fulfilled. 
(Note in Insomnia, Header added to Accept - application/json)
Send:
  ```
  {
    "name": null
  }
  ```
Receive: 
  ```
  {
    "message": "The name field is required.",
    "errors": {
      "name": [
        "The name field is required."
      ]
    }
  }
  ```
Additionaly, we can use the $validated array to declare the name variable, since we have specfied that name is required in the validation:
  ```
  ...
            'name' => 'required|string|max:80|unique:categories,name',  
        ]);
        $category = new Category();
        $category->name = $validated['name'];
  ...
  ```
Send:
  ```
  {
    "name": "John"
  }
  ```
Receive:
  ```
 {
    "message": "The name has already been taken.",
    "errors": {
      "name": [
        "The name has already been taken."
      ]
    }
  }
  ```
<br>

## **CRUD: Return a list of the resource contents** ##
### from Controller, method index() ###
<br>

Get resources from a table and choose how to order them. 
  ```
    public function index()
      {
          $categories = Category::orderBy('name', 'DESC')->get();       // ASC by default
          return response() -> json([
              'categories' => $categories,
          ]);
      }
  ```
From Insomnia or Postman, create a GET request with no body.<br>
<span style="color:yellow">Here, we receive an error, because we haven't yet defined a Route in api.php for GET</span>

  ```
  {
  "message": "The GET method is not supported for this route. Supported methods: POST.",
  ...
  ```
Once fixed, full list of resources in table is returned.

## **CRUD: Show one resource or document from table** ##
### from Controller, method show() ###
<br>

Thanks to dependency injection in Laravel, the method 'show' receives the Model as an argument (not a Request, as with index and store).

We can specify a specific resource using URL parameters in the Route. 
Example:
  ```
  Route::get('/categories/{id}', [CategoriesController::class, 'show']);
  ```
Then, in the 'show' method, if we change the arguments to reflect the new route, and send a request specifying our URL parameter, we can show one resource. <br>
Example: GET http://localhost:8000/api/categories/2<br>
Controller:
  ```
  public function show(int $id)
      {
          return response()->json([
            'category' => $id,
          ]);
      }
  ```
Received:
  ```
  {
    "category": 2
  }
  ```
To make things easier, you can instead add the Model as a URL parameter in the Route.
Then in the 'show' declaration, Laravel will automatically recognise the parameter as a 'key' from the Model, and you will get the full resource for the parameter passed in the URL with the GET request.
For example:
  ```
  Route::get('/categories/{category}', [CategoriesController::class, 'show']);
  ```
Controller:
  ```
  public function show(Category $category)
      {
          return response()->json([
            'category' => $category,
          ]);
      }
  ```
Receive:
  ```
  {
    "category": {
      "id": 3,
      "name": "Categoría 2",
      "created_at": "2022-09-28T22:16:46.000000Z",
      "updated_at": "2022-09-28T22:16:46.000000Z"
    }
  }
  ```

## **CRUD: Remove a resource or document from table** ##
### from Controller, method destroy() ###
<br>

First, add the Route:
  ```
  Route::delete('/categories/{category}', [CategoriesController::class, 'destroy']);
  ```
Add function to controller:
  ```
      public function destroy(Category $category)
      {
          $category->delete();
          return response()->json([
              'success' => true,
          ]);
      }
  ```
Send DELETE petition and add resource as URL param: DELETE http://localhost:8000/api/categories/3
Receive:
  ```
  {
    "success": true
  }
  ```


## **CRUD: Update a resource from table** ##
### from Controller, method update() ###
<br>

The 'update' method takes two arguments, the Request (from the user) and also the Model.
The order of the arguments does not matter, all is resolved by dependency injection in Laravel.

The basic controller function might look like this:
  ```
    public function update(Request $request, Category $category)
      {
          return response()->json([
              'success' => true,
              'category' => $category,
          ]);
      }
  ```

Then, update the route to see a basic response:
```
Route::patch('/categories/{category}', [CategoriesController::class, 'update']);
```

Send to PATCH http://localhost:8000/api/categories/2:
  ```
  {
    "name": "John"
  }
  ```
Receive: 
  ```
  {
    "success": true,
    "category": {
      "id": 2,
      "name": "Categoria 1",
      "created_at": "2022-09-28T22:13:39.000000Z",
      "updated_at": "2022-09-28T22:13:39.000000Z"
    }
  }
  ```

To specify that the name be changed, we can add the 'name' field anv validation to the function:
  ```
      public function update(Request $request, Category $category)
      {
          $validated = $request->validate([
              'name' => 'required|string|max:80|unique:categories,name',
          ]);
          $category->name = $validated['name'];
          $category->save();
          return response()->json([
              'success' => true,
              'category' => $category,
          ]);
      }
  ```
Then sending the PATCH request to id 2 parameter:
  ```
  {
    "name": "Daniela"
  }
  ```
Receive:
  ```
  {
    "success": true,
    "category": {
      "id": 2,
      "name": "Daniela",
      "created_at": "2022-09-28T22:13:39.000000Z",
      "updated_at": "2022-09-29T15:19:30.000000Z"
    }
  }
  ```
  If you try to send a PATCH twice with the same data, you'll get this <span style="color:yellow">error</span>:
  ```
  {
	"message": "The name has already been taken.",
	"errors": {
		"name": [
			"The name has already been taken."
		]
	}
}
```
To avoid this, you need to add this resource id to the validation, like this:
  ```
    $validated = $request->validate([
              'name' => 'required|string|max:80|unique:categories,name' . $category->id,
          ]);
  ```
It will now update.

### **CRUD now completed for Category Model.** ###
<br>

<hr><br>

## **CRUD: Creating a resource for Product Model** ##

If you want to create a controller without the methods added, you can simply enter the command without the --api -m flags.

This way you can decide what functionalities to add. In this case, for example:
- ProductsController will be able to:
  - add a product to a specific Category

In this case, the product validation is a bit more complicated. Note the category_id field requires that the given id exists as a category id.
Example:
  ```
    public function store(Request $request)
      {   
          $validated = $request->validate([
              'name' => 'required|string|max:255',
              'description' => 'sometimes|nullable|string|min:5',
              'price_in_cents' => 'required|integer|min:1',
              'category_id' => 'required|exists:categories,id',
          ]);        
          return response()->json([
              'success' => true,
              'validated' => $validated,
          ]);
      }
  ```
Sent to POST http://localhost:8000/api/products"

<br>
Note sqlite is tricky with integer values

```
{
	"name": "Product Name 8",
	"price_in_cents": "100",      
	"category_id": 1
}
```
Receive:
```
{
	"success": true,
	"validated": {
		"name": "Product Name 8",
		"price_in_cents": "100",
		"category_id": 1
	}
}

```
To return the product, change the $validated value for $product value:

```
// 'validated' => $validated,
'product' => $product,
```

### Define a relationship between tables ###

<i> 

#### - **from product to category** - ####

</i>
In this case, a product has a category, and a category may have many products. 
To define a relationship, a method is created in the model.
Example with Product:

  ```
  ...
  use HasFactory;

      public function category()
      {
          return $this->belongsTo(Category::class);
      }
  ```
Then complete the relationship in the Controller, after save(), in this case we're loading category.

If you only wante certain fields, add colon followed by fields you want to pull.

Example:
  ```
  ...
  $product->save();
  $product->load('category:id,name');
  ...
  ```


<i>

#### - **Inverse relationship, from category to product** - ####

</i>
In this case, create a method in the Category model. Here we use 'hasMany' method, because our Category model can have many products that match that category.

<i>Note: when using <span style="color:yellow">'::class'</span>, if the referenced file is not in the same folder, it will have to be <span style="color:yellow">imported at the top of the file.</span></i>

  ```
  ...
  use HasFactory;

      public function products()    // plural because there will be many products
      {
          return $this->hasMany(Product::class);
      }
  ...
  ```
Now, we can go to the CategoriesController and load our products into the show() method.
  ```
  ...
  $category->load('products');

          return response()->json([
            'category' => $category,
  ```
- <i>Alternately</i>, we could choose for the show() method to receive as an argument the id of the category, instead of the Category model.
- We can use find() method to return the response, but if the Category is null or doesn't exist, this won't return an error. To fix, you can use findOrFail().
  ```
    public function show(int $id)
    {
        $category = Category::findOrFail($id);
        $category->load('products'); 
        
        return response()->json([
          'id' => $id,
          'category' => $category,
        ]);
    }
  ```
Receive:
  ```
{
	"id": 1,
	"category": {
		"id": 1,
		"name": "pop",
		"created_at": "2022-09-30T09:51:55.000000Z",
		"updated_at": "2022-09-30T09:51:55.000000Z"
	}
  ```

#### - **Eager-Loading, relationship from category to product** - ####
See [below](/Concepts): 

We can use eager-loading and the 'with()' method to load the relationships before loading the Model (Category, in this case). This is like load, but the product relationships will be loaded before the category.
In the above method for show(), it would look like this:
```
public function show(int $id)
    {
        $category = Category::with('products')->findOrFail($id);
        // remove load line
        
        return response()->json([
...
```
However, this is more commonly used with relationships to multiple fields. We could implement it in the index() method.

<i>Version 1</i>:
The drawback is that this creates 3 queries to the database.

```
 public function index()
    {
        $categories = Category::orderBy('name')->get(); // ASC by default
        foreach ($categories as $category) {
            $category->load('category');
        }
        return response() -> json([
            'categories' => $categories,
        ]);
    }
```
<i>Version 2 (improved) </i>:
This returns the same, but is much more optimized.
The advantage is that no matter how many results, you still make one query.

  ```
      public function index()
      {
          $categories = Category::with('products')->orderBy('name')->get(); // ASC by default
        
          return response() -> json([
  ```

#### - **Many-to-many relationships** - ####

Case: A category has many products and a product has many categories.
Create a 'pilot' model using both models.
Naming convention: Capitalized model names in singular and alphabetical, in this case with:
Here, we would create the model, which would extend Pivot, and the -m tag creates the migration, which we can then edit.
  ```
  php artisan make:model CategoryProduct --pivot -m
  ```
Adding scheme to migration:
  ```
    $table->id();
    $table->foreignId('category_id');   // alt: foreignIdFor(Category::class)
    $table->foreignId('product_id');   // alt: foreignIdFor(Product::class)
    $table->timestamps();
  ```
Return to the Category and Product models and instead of 'hasMany', use 'belongsToMany'.

In Category.php, that is all you need to change:
  ```
    ...
   public function products()    // plural because there will be many products
    {
        return $this->belongsToMany(Product::class);
    }
    ...

  ```
In Product.php, you need to change the function to plural 'categories', and add 'belongToMany()':
  ```
  public function categories()
      {
          return $this->belongsToMany(Category::class);
      }
  ```
<i>**This doesn't work alone, see documentation to complete many-to-many relationships**</i>


## Concepts ## 

- Remember private functions can only be called from within their scope, public functions are accessible everywhere.
- Eager-loading vs. Lazy-loading: 
  - Eager-loading: operations are carried out when asked / executed.
    - Application: When submitting queries to the database, if you want multiple fields from a table, you could do this in two ways:

      a. submit queries one by one for each of three fields, for example.

      b. submit one query in place of all three queries, and deal with the results received -> <i>Eager-Loading</i>. In Laravel, this refers to loading the relationships before getting the Model. Usually used when loading multiple fields.
  - Lazy-loading: operations are carried out when required.
- Illuminate: 
  - Laravel's database engine and namespace, now incorporated fully into Laravel.
- Eloquent:
  - ORM (Object Relational Mapper) included by default in the Laravel framework.
- Laravel Octane:
  - brings asynchronous to Laravel
 



# *Useful Programs and VSCode Extensions** #

- SQLite Viewer
- Laravel Blade Snippets
- Tailwind
- Insomnia / Postman





