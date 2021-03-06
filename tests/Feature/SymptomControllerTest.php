<?php

namespace Tests\Feature;

use function MongoDB\BSON\toJSON;
use Tests\TestCase;

use App\Symptom;
use Faker;

//use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Foundation\Testing\DatabaseTransactions;


use DB;
use App\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

/**
 * Class SymptomControllerTest
 *
 * 1. Test that you must be logged in to access any of the controller functions.
 *
 * @package Tests\Feature
 */
class SymptomControllerTest extends TestCase
{

    //use RefreshDatabase;
    //------------------------------------------------------------------------------
    // Test that you must be logged in to access any of the controller functions.
    //------------------------------------------------------------------------------

    /**
     * @test
     */
    public function prevent_non_logged_in_users_from_seeing_symptom_index()
    {
        $response = $this->get('/symptom');

        $this->withoutMiddleware();
        $response->assertRedirect('login');
    }

    /**
     * @test
     */
    public function prevent_non_logged_in_users_from_creating_symptom()
    {
        $response = $this->get(route('symptom.create'));

        $this->withoutMiddleware();
        $response->assertRedirect('login');
    }

    /**
     * @test
     */
    public function prevent_non_logged_in_users_from_storing_symptom()
    {
        $response = $this->get(route('symptom.store'));

        $this->withoutMiddleware();
        $response->assertRedirect('login');
    }

    /**
     * @test
     */
    public function prevent_non_logged_in_users_from_showing_symptom()
    {
        // Should check for permisson before checking to see if record exists
        $response = $this->get(route('symptom.show', ['id' => 1]));

        $this->withoutMiddleware();
        $response->assertRedirect('login');
    }

    /**
     * @test
     */
    public function prevent_non_logged_in_users_from_editing_symptom()
    {
        // Should check for permisson before checking to see if record exists
        $response = $this->get(route('symptom.edit', ['id' => 1]));

        $this->withoutMiddleware();
        $response->assertRedirect('login');
    }


    /**
     * @test
     */
    public function prevent_non_logged_in_users_from_updateing_symptom()
    {
        // Should check for permisson before checking to see if record exists
        $response = $this->put(route('symptom.update', ['id' => 1]));
        $this->withoutMiddleware();
        $response->assertRedirect('login');
    }


    /**
     * @test
     */
    public function prevent_non_logged_in_users_from_destroying_symptom()
    {

        // Should check for permisson before checking to see if record exists
        $response = $this->delete(route('symptom.destroy', ['id' => 1]));

        $this->withoutMiddleware();
        $response->assertRedirect('login');
    }

    //------------------------------------------------------------------------------
    // Test that you must have access any of the controller functions.
    //------------------------------------------------------------------------------


    /**
     * @test
     */
    public function prevent_users_without_permissions_from_seeing_symptom_index()
    {

        $user = $this->getRandomUser('cant');

        $response = $this->actingAs($user)->get('/symptom');

        // TODO: Check for message???

        $response->assertRedirect('home');
    }

    /**
     * @test
     */
    public function prevent_users_without_permissions_from_creating_symptom()
    {

        $user = $this->getRandomUser('cant');

        $response = $this->actingAs($user)->get(route('symptom.create'));

        $response->assertRedirect('home');
    }


    /**
     * @test
     */
    public function prevent_users_without_permissions_from_storing_symptom()
    {

        $user = $this->getRandomUser('cant');

        $response = $this->actingAs($user)->post(route('symptom.store'));

        $response->assertStatus(403);  // Form Request::authorized() returns 403 when user is not authorized

    }

    /**
     * @test
     */
    public function prevent_users_without_permissions_from_showing_symptom()
    {

        $user = $this->getRandomUser('cant');

        // Should check for permisson before checking to see if record exists
        $response = $this->actingAs($user)->get(route('symptom.show', ['id' => 1]));

        $response->assertRedirect('home');
    }

    /**
     * @test
     */
    public function prevent_users_without_permissions_from_editing_symptom()
    {

        $user = $this->getRandomUser('cant');

        $response = $this->actingAs($user)->get(route('symptom.edit', ['id' => 1]));

        $response->assertRedirect('home');
    }


    /**
     * @test
     */
    public function prevent_users_without_permissions_from_updateing_symptom()
    {

        $user = $this->getRandomUser('cant');

        $response = $this->actingAs($user)->put(route('symptom.update', ['id' => 1]));

        $response->assertStatus(403);  // Form Request::authorized() returns 403 when user is not authorized

    }


    /**
     * @test
     */
    public function prevent_users_without_permissions_from_destroying_symptom()
    {

        $user = $this->getRandomUser('cant');

        // Should check for permisson before checking to see if record exists
        $response = $this->actingAs($user)->delete(route('symptom.destroy', ['id' => 1]));

        $response->assertRedirect('home');
    }

    ////////////

    //------------------------------------------------------------------------------
    // Test that you must have access any of the controller functions
    //   user does have access to index
    //------------------------------------------------------------------------------


    /**
     * @test
     */
    public function prevent_users_withonly_index_permissions_from_creating_symptom()
    {

        $user = $this->getRandomUser('only index');

        $response = $this->actingAs($user)->get(route('symptom.create'));

        $response->assertRedirect('symptom');
    }


    /**
     * @test
     */
    public function prevent_users_withonly_index_permissions_from_storing_symptom()
    {

        $user = $this->getRandomUser('only index');

        $response = $this->actingAs($user)->post(route('symptom.store'));

        $response->assertStatus(403);  // Form Request::authorized() returns 403 when user is not authorized

    }

    /**
     * @test
     */
    public function prevent_users_withonly_index_permissions_from_showing_symptom()
    {

        $user = $this->getRandomUser('only index');

        // Should check for permisson before checking to see if record exists
        $response = $this->actingAs($user)->get(route('symptom.show', ['id' => 1]));

        $response->assertRedirect('symptom');
    }

    /**
     * @test
     */
    public function prevent_users_withonly_index_permissions_from_editing_symptom()
    {

        $user = $this->getRandomUser('only index');

        $response = $this->actingAs($user)->get(route('symptom.edit', ['id' => 1]));

        $response->assertRedirect('symptom');
    }


    /**
     * @test
     */
    public function prevent_users_withonly_index_permissions_from_updating_symptom()
    {

        $user = $this->getRandomUser('only index');

        $response = $this->actingAs($user)->put(route('symptom.update', ['id' => 1]));

        $response->assertStatus(403);  // Form Request::authorized() returns 403 when user is not authorized

    }


    /**
     * @test
     */
    public function prevent_users_withonly_index_permissions_from_destroying_symptom()
    {

        $user = $this->getRandomUser('only index');

        // Should check for permisson before checking to see if record exists
        $response = $this->actingAs($user)->delete(route('symptom.destroy', ['id' => 1]));

        $response->assertRedirect('symptom');
    }

    /// ////////

    //------------------------------------------------------------------------------
    // Now lets test that we have the functionality to add, change, delete, and
    //   catch validation errors
    //------------------------------------------------------------------------------
    /**
     * @test
     */
    public function prevent_showing_a_nonexistent_symptom()
    {
        // get a random user
        $user = $this->getRandomUser('super-admin');

        // act as the user we got and request the create_new_article route
        $response = $this->actingAs($user)->get(route('symptom.show',['id' => 100]));

        $response->assertSessionHas('flash_error_message','Unable to find Symptoms to display.');

    }

    /**
     * @test
     */
    public function prevent_editing_a_nonexistent_symptom()
    {
        // get a random user
        $user = $this->getRandomUser('super-admin');

        // act as the user we got and request the create_new_article route
        $response = $this->actingAs($user)->get(route('symptom.edit',['id' => 100]));

        $response->assertSessionHas('flash_error_message','Unable to find Symptoms to edit.');

    }




    /**
     * @test
     */
    public function it_allows_logged_in_users_to_create_new_symptom()
    {
        // get a random user
        $user = $this->getRandomUser('super-admin');

        // act as the user we got and request the create_new_article route
        $response = $this->actingAs($user)->get(route('symptom.create'));

        $response->assertStatus(200);
        $response->assertViewIs('symptom.create');
        $response->assertSee('symptom-form');

    }

    /**
     * @test
     */
    public function prevent_creating_a_blank_symptom()
    {
        // get a random user
        $user = $this->getRandomUser('super-admin');

        $data = [
            'id' => "",
            'name' => "",
        ];

        $totalNumberOfSymptomsBefore = Symptom::count();

        $response = $this->actingAs($user)->post(route('symptom.store'), $data);

        $totalNumberOfSymptomsAfter = Symptom::count();
        $this->assertEquals($totalNumberOfSymptomsAfter, $totalNumberOfSymptomsBefore, "the number of total article is supposed to be the same ");

        $errors = session('errors');
        $this->assertEquals($errors->get('name')[0],"The name field is required.");

    }

    /**
     * @test
     *
     * Check validation works
     */
    public function prevent_invalid_data_when_creating_a_symptom()
    {
        // get a random user
        $user = $this->getRandomUser('super-admin');

        $data = [
            'id' => "",
            'name' => "a",
        ];

        $totalNumberOfSymptomsBefore = Symptom::count();

        $response = $this->actingAs($user)->post(route('symptom.store'), $data);

        $totalNumberOfSymptomsAfter = Symptom::count();
        $this->assertEquals($totalNumberOfSymptomsAfter, $totalNumberOfSymptomsBefore, "the number of total article is supposed to be the same ");

        $errors = session('errors');

        $this->assertEquals($errors->get('name')[0],"The name must be at least 3 characters.");

    }

    /**
     * @test
     *
     * Check validation works
     */
    public function create_a_symptom()
    {

        $faker = Faker\Factory::create();
        // get a random user
        $user = $this->getRandomUser('super-admin');

        $data = [
          'name' => $faker->name,
        ];

        info('--  Symptom  --');
         info(print_r($data,true));
          info('----');

        $totalNumberOfSymptomsBefore = Symptom::count();

        $response = $this->actingAs($user)->post(route('symptom.store'), $data);

        $totalNumberOfSymptomsAfter = Symptom::count();


        $errors = session('errors');

        info(print_r($errors,true));

        $this->assertEquals($totalNumberOfSymptomsAfter, $totalNumberOfSymptomsBefore + 1, "the number of total symptom is supposed to be one more ");

        $lastInsertedInTheDB = Symptom::orderBy('id', 'desc')->first();


        $this->assertEquals($lastInsertedInTheDB->name, $data['name'], "the name of the saved symptom is different from the input data");


    }

    /**
     * @test
     *
     * Check validation works
     */
    public function prevent_creating_a_duplicate_symptom()
    {

        $faker = Faker\Factory::create();

        // get a random user
        $user = $this->getRandomUser('super-admin');


        $totalNumberOfSymptomsBefore = Symptom::count();

        $symptom = Symptom::get()->random();
        $data = [
            'id' => "",
            'name' => $symptom->name,
        ];

        $response = $this->actingAs($user)->post(route('symptom.store'), $data);
        $response->assertStatus(302);

        $errors = session('errors');
        $this->assertEquals($errors->get('name')[0],"The name has already been taken.");

        $totalNumberOfSymptomsAfter = Symptom::count();
        $this->assertEquals($totalNumberOfSymptomsAfter, $totalNumberOfSymptomsBefore, "the number of total symptom should be the same ");

    }

    /**
     * @test
     *
     * Check validation works
     */
    public function allow_changing_symptom()
    {

        $faker = Faker\Factory::create();

        // get a random user
        $user = $this->getRandomUser('super-admin');

        $data = Symptom::get()->random()->toArray();

        $data['name'] = $data['name'] . '1';

        $totalNumberOfSymptomsBefore = Symptom::count();

        $response = $this->actingAs($user)->json('PATCH', 'symptom/' . $data['id'], $data);

        $response->assertStatus(200);

        $totalNumberOfSymptomsAfter = Symptom::count();
        $this->assertEquals($totalNumberOfSymptomsAfter, $totalNumberOfSymptomsBefore, "the number of total symptom should be the same ");

    }



    /**
     * @test
     *
     * Check validation works on change for catching dups
     */
    public function prevent_creating_a_duplicate_by_changing_symptom()
    {

        $faker = Faker\Factory::create();

        // get a random user
        $user = $this->getRandomUser('super-admin');

        $data = Symptom::get()->random()->toArray();



        // Create one that we can duplicate the name for, at this point we only have one symptom record
        $symptom_dup = [

            'name' => $faker->name,
        ];

        $response = $this->actingAs($user)->post(route('symptom.store'), $symptom_dup);


        $data['name'] = $symptom_dup['name'];

        $totalNumberOfSymptomsBefore = Symptom::count();

        $response = $this->actingAs($user)->json('PATCH', 'symptom/' . $data['id'], $data);
        $response->assertStatus(422);  // From web page we get a 422

        $errors = session('errors');

        info(print_r($errors,true));

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.'
            ]);

        $response->assertJsonValidationErrors(['name']);

        $totalNumberOfSymptomsAfter = Symptom::count();
        $this->assertEquals($totalNumberOfSymptomsAfter, $totalNumberOfSymptomsBefore, "the number of total symptom should be the same ");

    }

    /**
     * @test
     *
     * Check validation works
     */
    public function allow_deleting_symptom()
    {

        $faker = Faker\Factory::create();

        // get a random user
        $user = $this->getRandomUser('super-admin');

        $data = Symptom::get()->random()->toArray();


        $totalNumberOfSymptomsBefore = Symptom::count();

        $response = $this->actingAs($user)->json('DELETE', 'symptom/' . $data['id'], $data);

        $totalNumberOfSymptomsAfter = Symptom::count();
        $this->assertEquals($totalNumberOfSymptomsAfter, $totalNumberOfSymptomsBefore - 1, "the number of total symptom should be the same ");

    }

    /**
     * Get a random user with optional role and guard
     *
     * @param null $role
     * @param string $guard
     * @return mixed
     */
    public function getRandomUser($role = null, $guard = 'web')
    {

        if ($role) {

            // This should work but throws a 'Spatie\Permission\Exceptions\RoleDoesNotExist: There is no role named `super-admin`.
            $role_id = Role::findByName($role,'web')->id;

            $sql = "SELECT model_id FROM model_has_roles WHERE model_type = 'App\\\User' AND role_id = $role_id ORDER BY RAND() LIMIT 1";
            $ret = DB::select($sql);
            $user_id = $ret[0]->model_id;

            $this->user = User::find($user_id);
        } else {
            $this->user = User::get()->random();
        }

        return $this->user;
    }


}
