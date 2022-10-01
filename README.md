# API Rest Workshop Part 2 - Advanced #

This CRUD setup guide was created working along with the second workshop at Assembler Institute of Technology, PHP Laravel - API Rest Advanced:

Original repo for the project is here:
https://github.com/assembler-institute/php-laravel-api-rest-workshop 


## Getting Started / Useful Commands: ##
<br>


- For basic information about setting up the Laravel project, see branch for Part One - Basic

### Routes ###

By using method apiResource in routes, you can eliminate the need for the five different routes created for Category in part one of the workshop (GET, GET, POST, PATCH, DELETE). Laravel detects them automatically.
You can replace all the routes in a path (for example, /categories) with one line:
  ```
  Route::apiResource('categories', CategoriesController::class);
  ```

## Definitions: ##

### Seeders ###
- Classes used to generate and insert sample data into the database

### Factories ###
- Classes used to define how models are populated with fake data, to not have to create names, emails, etc. (using Faker)
<br>

## Creating Factories ##

Naming convention: name of model capitalized, followed by 'Factory'.

This command creates the factory file, in the database folder, ready for adding the definition function.
  ```
  php artisan make:factory CategoryFactory
  ```
Then we decide what columns the Model factory has and what values they should have.
  ```
  public function definition()
      {
          return [
              'name' => $this->faker->name,
          ];
      }
  ```
Next, we use a seeder in order to use the factory, with the same naming convention as the factory.
This creates the seeder file in seeders folder in the database folder.
```
php artisan make:seeder CategorySeeder
```
Inside the file, you designate what categories you will need in the database. For example, we can create ten categories at once. To access the factories from the seeder files, remember we include the 'use HasFactory' line in each of our Models. (use goes with traits in Laravel, which we can apply or import in different parts our code).

Note: I needed to import 'use App\Models\Category;' at the top, otherwise Category returns undefined.

In CategorySeeder.php:
  ```
  public function run()
      {
          Category::factory(10)->create();
      }
  ```
To use, migrate:fresh to clear the database, and then: php artisan db:seed <SeederName>
  ```
  php artisan db:seed CategorySeeder
  ```
Next, since our Product model has more fields and a foreign id, the seeding and factory process is a bit more complicated.
First, as with Category, create the factory with artisan make:factory and then add the definition:
  ```
  ...
          return [
              'name' => $this->faker->name,
              'description' => $this->faker->text(150),
              'price_in_cents' => $this->faker->randomNumber(3),
              'category_id' => Category::factory(),  // SEE NOTE
          ];
      }
  ```
Defining the 'category_id' column in this way will create a new category for each product. Here, it would be better to add a category manually to each created product, by changing the CategorySeeder file. We can add this to the function run() from above:
  ```
  public function run()
      {
          $categories = Category::factory(10)->create();
          foreach ($categories as $category) {
              Product::factory(5)->create([
                  'category_id' => $category->id,
              ]);
          }
  ```
When we migrate:fresh and call the category seeder, php artisan db:seed CategorySeeder, we will see the 
output with random categories for each product.

### Quick Seeding ###

Since this is a common process, a faster command is: php artisan db:seed to seed the data, OR php artisan migrate:fresh --seed, to wipe the databse and then seed.

To use these commands, we need to complete the run() method which they call, which is in the DatabaseSeeder class, in DatabaseSeeder.php. There, we can write our code in the run method, which will call all the Seeders that we include:
  ```
    public function run()
      {
        $this->call([
          CategorySeeder::class,
        ]);
      }
  ```
## Feature and Unit Testing in Laravel ##

By default, the testing package used in Laravel is PHPUnit. Another option is Pest.
For testing in browser like Cypress, Laravel uses built-in Dusk package.

- Unit Tests test functions that don't touch the database and in general provide a pure result.


### **Unit Test Example (formatted price):** ###

Command: php artisan make:test ProductPriceTest --unit
  - name must end in Test
  - --unit flag specifies this is a unit test
  - creates test file in Tests/Unit/ directory
  - the default test will return pass, because the default function in the test file tests if boolean true is true, check with: php artisan test

Next, we define the function to format the price, and then define the test itself.

- We create a service to format the price.
  - Go to App and create Services folder and file.
  - If you don't use artisan to create the file you need to create it yourself.
    - Add a namespace. PHP convention is that namespace is same as parent folder.
    - Then add the service function.
    ```
    <?php
    namespace App\Services;
    class ProductPriceService {
      public function format(int $cents: string)
      {
        return number_format($cents / 100, 2, '.', ',') . '€';
      }
    }
    ```
- After creating the service, you can go to the test file. The test methods must begin with test_ (underscore). In the test method / function, create a $formatted variable, and then call teh format method on it (which was just created in ProductPriceService.php);
  ```
    public function test_price_is_correctly_formatted()
    {
        $formatter = new ProductPriceService();

        $formatter->format(100); // 1,00 €
    }
  ```
If we call this test, we get an error that 'test did not perform any assertions'. Assertions are used to inform the test that if it is not passed, to inform the user of the error and reason for failure.
  ```
  WARN  Tests\Unit\ProductPriceTest
    ! price is correctly formatted → This test did not perform any assertions
  ```

### To create an assertion for this case: ###

Use the assert method. Add / Change the code to the the above function in ProductPriceTest.php, and we can add other cases as well:
  ```
    public function test_price_is_correctly_formatted()
      {
          $formatter = new ProductPriceService();

          $this->assertEquals('1.00 €', $formatter->format(100));
          $this->assertEquals('0,01 €', $formatter->format(1));
          $this->assertEquals('1.05 €', $formatter->format(105));
          $this->assertEquals('1.050,00 €', $formatter->format(105000));
          $this->assertEquals('-10.000,00 €', $formatter->format(-10000));
      }
  ```
If we want to add a condition that our prices are always positive, for example, we can modify the original function in ProductPriceService.php to throw an error if negative:
  ```
    public function format(int $cents): string
    {
      if ($cents < 0) {
        throw new Exception('Price cannot be negative');
      } 
      return number_format($cents / 100, 2, '.', ',') . '€';
    }
  ```
At this point, running the test will return the exception:
  ```
    • Tests\Unit\ProductPriceTest > price is correctly formatted
    PHPUnit\Framework\ExceptionWrapper 

    Price cannot be negative
  ```
So we add another function to the test file, ProductPriceTest.php. The expected exception matches to the exception in the class: 'Price cannot be negative':
  ```
  public function test_negative_prices_are_not_allowed()
      {
          $this->expectException(Exception::class);  

          $formatter = new ProductPriceService();
          $formatter->format(-100);
      }
  ```
<br>

### **Feature Test Example 1 / categories and their products:** ###
<br>

- Useful for testing API functions, here we can test all of them.

Command: php artisan make:test CategoriesApiTest
  - name must end in Test
  - no flag defaults to Feature Test folder
  - test in case of API shows default function for testing GET for a return response of 200.
  - the feature test files include two imports by default, RefreshDatabase and Withfaker.
    - RefreshDatabase ensure you start the test with a clean database.

First, add 'use RefreshDatabase'. First test if endpoint exists. 

Test class and function will look like:
  ```
    class CategoriesApiTest extends TestCase
    {
        use RefreshDatabase;

        public function test_endpoint_exists()
        {
            $response = $this->get('/api/categories');
            $response->assertStatus(200);
        }
    }
  ```
Run the test. You can use a filter to run only one test, using the --filter flag and the name of the class, or even the name of the function.

Here, run with class name: 'php artisan test --filter CategoriesApiTest'.

Test Result:
  ```
    PASS  Tests\Feature\CategoriesApiTest
    ✓ endpoint exists
  ```
Another test could test the structure of the response, that it returns a json with a specific structure.

Set the $response variable again to get the path.
Note there are methods you can call that...
  - the response contains a specific cookie
  - that the response is 201 for every resource created
  - that a text returned (for example, html) contains or doesn't contain certain text 
  - that a json returned has a specific structure
  - etc.

In order to test this response, we also need to test the endpoint for category creation, in order to return those catetgories and check the structure.

Function for checking json structure contains array for categories:
  ```
  public function test_response_structure_contains_categories()
      {
          $response = $this->get('/api/categories');
          $response->assertExactJson([
              'categories' => []
          ]);
      }
  ```

Function testing the creation and fetching of categories. Here we could run by creating as many as we want. My tests failed at over 2000 entries.

We can also add lines to test other assertions, for example status 200.
  ```
    public function test_returns_all_categories()
      {
          Category::factory(500)->create();
          $response = $this->get('/api/categories');
          $response->assertJsonCount(500, 'categories');
          $response->assertStatus(200);
      }
  ```

Another thing we could test is that each category returned has the fields 'id', 'name', and 'products'.

To do this, create another test.
  - start be using factory to create a category.
  - create a $response to get the categories that exist.
  - create a $returnedCategory for the categories in the response
  - then assert that the response category has the same name and id as the one created, and that the category's products are an array.
  - to check that the products are the same, we would have to also create products for that category.
  - so create a product factory for 10 products, and assign them to that category.
  - again we have to make the request, check that the index still exists, that products returned are an array, and that 10 of them are returned.
  - note the subindex can be accessed with dot notation

Code:
  ```
  public function test_categories_have_products()
      {
          $createdCategory = Category::factory()->create();

          $response = $this->get('/api/categories');
          $returnedCategory = $response->json('categories')[0];

          $this->assertEquals($createdCategory->name, $returnedCategory['name']);
          $this->assertEquals($createdCategory->id, $returnedCategory['id']);
          $this->assertIsArray($returnedCategory['products']);

          $product = Product::factory(10)->create([
              'category_id' => $createdCategory->id,
          ]);

          $response = $this->get('/api/categories');
          $returnedCategory = $response->json('categories')[0];
          $this->assertIsArray($returnedCategory['products']);
          $response->assertJsonCount(10, 'categories.0.products');
      }
  ```
Test returns:
  ```
    PASS  Tests\Feature\CategoriesApiTest
    ✓ endpoint exists
    ✓ response structure contains categories
    ✓ returns all categories
    ✓ categories have products

    Tests:  4 passed
    Time:   0.56s
  ```
<br>

### **Formatting our price in the database:** ###
<br>

- We created a Price formatting service in ProductPriceService.php.
- Since price in the database is stored in cents, it would be nice to return from the response an already formatted price. This way the frontend does not have to adapt to what is returned from the backend.
<br>
- Eloquent offers computed methods / attributes that help us in these cases.

Go to the Product Model,  Prouct.php
  - give the method a name and specify that it will return an Eloquent Attribute
  - return an attribute that will have a getter that returns the Price Service we created, formatting the price_in_cents.
  ```
  public function formattedPrice(): Attribute
      {
          return Attribute::make(
              get: fn () => (new ProductPriceService())->format($this->price_in_cents),
          );
      }
  ```
  - Link to Laravel reference docs: **[Mutators & Casting](https://laravel.com/docs/9.x/eloquent-mutators)**
    - see Accessors, Mutators
    - see difference between Getters and Setters in Laravel
<br/>
  - **Note that** although we created the method in the Product Model, the price will **still not be formatted in the database** ('php artisan migrate:fresh --seed' to check).
    - We have to tell Eloquent that when the object from php is serialized (turning the object to text, in this case json), that it has to add the formatted price.
    - Link docs **[serialization](https://laravel.com/docs/9.x/eloquent-serialization)**
    - To do this, we have to add the attribute name to the appends property of our model. You an use snake case or camel case, but snake case is the convention with $appends.

    ```
      $appends = [
        'formatted_price'    
      ];
    ```
    Now the response includes the formatted price:

    ```
    "formatted_price": "5,33 €"
    ```
In the end, these Attributes are very useful for later accessing these variables in the rest of your code by calling $product->formattedPrice, to access the Getter that we added to the Product model.
<br>

### **Feature Test Example 2 / service created for formatting prices:** ###
<br>

For this test, we can use the existing CategoriesApiTest.php.

- Create the Category
- Create the Product
- Get the response and get the products 0 index, since other functionalities for categories and products have been tested in other tests.
- Test that the product has 'formatted_price' with 'assertArrayHasKey'.
- Test that the 'formatted_price' is equal to the result of calling the format method from the ProductPriceService class on the 'price_in_cents' key.
- You could also add tests for the 'price_in_cents' key, for example ArrayHasKey and IsInt.

  ```
  public function test_products_have_formatted_price()
      {
          $createdCategory = Category::factory()->create();
          $product = Product::factory()->create([
              'category_id' => $createdCategory->id,
          ]);
          $response = $this->get('/api/categories');
          $returnedCategory = $response->json('categories.0.products.0');
          
          $this->assertArrayHasKey('formatted_price', $returnedCategory);
          $this->assertEquals((new ProductPriceService())->format($product->price_in_cents), $returnedCategory['formatted_price']);
          $this->assertArrayHasKey('price_in_cents', $returnedCategory);
          $this->assertIsInt($returnedCategory['price_in_cents']);
      }
  ```
  Test result:
  ```
    PASS  Tests\Feature\CategoriesApiTest
  ✓ endpoint exists
  ✓ response structure contains categories
  ✓ returns all categories
  ✓ categories have products
  ✓ products have formatted price

  Tests:  5 passed
  ```







## Notes ##

- Eloquent attributes save a ton of time.


### Useful Packages ###

- Faker: gets fake data for seeder and factory classes



