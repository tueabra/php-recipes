<?php

/* UTILS */

function safe_rgx($str) {
    /* Convert string to safe regular expression */
    /* TODO: This is not actually safe, but covers our immediate needs */
    return '@^'.$str.'$@';
}

/* NOT-AT-ALL ORM */

$db = new mysqli('localhost', 'root', '', 'recipes');

class NAAOrmModel {
    /**
     *  Not-At-All ORM is a simple ORM without any type of validation or
     *  optimization.
     *
     *  It is only implemented as a Proof-Of-Concept, as we don't wan't to rely
     *  on external libraries.
     */

    public function __construct($values) {
        /**
         * Constructor for instantiating models with initial values.
         */
        foreach($values as $key => $value)
            $this->$key = $value;
    }

    public static function create($values) {
        /**
         * Create a new model.
         * @var values The initial values
         */
        $keys = [];
        $vals = [];
        foreach($values as $key => $value) {
            $keys[] = '`'.$key.'`';
            $vals[] = "'".$value."'";
        }
        $sql = 'INSERT INTO '.static::$__tablename.' ('.implode($keys, ',').') VALUES ('.implode($vals, ',').')';
        $db->query($sql) or die($db->error);
        return static::get($db->insert_id);
    }

    public static function filter($wheres=NULL, $limit=NULL, $distinct=NULL) {
        /**
         * Generic filter function for getting data from the database.
         * @var wheres An array of values to filter with (optional)
         * @var limit The maximum numbers of results to return (optional)
         * @var distinct A column from which to return distinct values.
         *      Note: If this is used, this method will return an array of
         *      values, not models.
         */
        global $db;

        $objs = [];
        $sql = 'SELECT ';
        if(!empty($distinct))
            $sql .= 'DISTINCT `'.$distinct.'`';
        else
            $sql .= '*';

        $sql .= ' FROM '.static::$__tablename;
        if(!empty($wheres)) {
            $sql .= ' WHERE';
            foreach($wheres as $key => $value)
                $sql .= ' `'.$key.'` = \''.$value.'\'';
        }

        if(!empty($limit))
            $sql .= ' LIMIT '.$limit;

        $res = $db->query($sql) or die($db->error);
        while($row = $res->fetch_assoc()) {
            if(!empty($distinct))
                $objs[] = $row[$distinct];
            else
                $objs[] = new static($row);
        }
        return $objs;
    }

    public static function distinct($variable) {
        /**
         * Convenience function for getting a list of distinct values.
         */
        return static::filter(NULL, NULL, $variable);
    }

    public static function get($id) {
        /**
         * Convenience function for getting one model only, selected by id.
         */
        return static::filter(array('id' => $id), 1)[0];
    } 

    public static function all() {
        /**
         * Convenience function for getting all models.
         */
        return static::filter();
    } 

    public function update($values) {
        /**
         * Updates the current model.
         * @var values The values to update, in an array
         */
        global $db;
        $vals = [];
        foreach($values as $key => $value) {
            $vals[] = '`'.$key.'` = \''.$value.'\'';
            $this->$key = $value;
        }
        $sql = 'UPDATE '.$this::$__tablename.' SET '.implode($vals, ',').' WHERE id = '.$this->id;
        $db->query($sql) or die($db->error);
    }
}

/* MODELS */

class Recipe extends NAAOrmModel {
    static $__tablename = 'recipe';

    public function delete() {
        /**
         * Delete the recipe and all associated data. This is normally a job for the ORM.
         */
        global $db;
        $db->query("DELETE FROM ingredient WHERE recipe_id = ".$this->id) or die($db->error);
        $db->query("DELETE FROM timing WHERE recipe_id = ".$this->id) or die($db->error);
        $db->query("DELETE FROM recipe WHERE id = ".$this->id) or die($db->error);
    }

    public function total_time() {
        /**
         * Get a number indicating the total time this recipes takes.
         */
        $total = 0;
        foreach(static::timings() as $time)
            $total += $time->minutes;
        return $total;
    }

    public function ingredients() {
        /**
         * Get a list of all the associated ingredients.
         */
        return Ingredient::filter(array('recipe_id' => $this->id));
    }

    public function timings() {
        /**
         * Get a list of all the associated timings
         */
        return Timing::filter(array('recipe_id' => $this->id));
    }
}

class Timing extends NAAOrmModel {
    static $__tablename = 'timing';

    public function delete() {
        /**
         * Delete the model.
         */
        global $db;
        $db->query("DELETE FROM timing WHERE id = ".$this->id) or die($db->error);
    }
}

class Ingredient extends NAAOrmModel {
    static $__tablename = 'ingredient';

    public function delete() {
        /**
         * Delete the model.
         */
        global $db;
        $db->query("DELETE FROM ingredient WHERE id = ".$this->id) or die($db->error);
    }
}

/* ROUTER */

class Router {
    /**
     * A very simple, proof-of-concept, URL router.
     * You can associate functions with one or more URLs (optionally containing
     * regular expressions. This will effectively encapsulate your different
     * pages as views.
     */

    private $routes = Array();

    public function route($url, $func) {
        /**
         * Add a new route.
         */
        $this->routes[$url] = $func;
    }

    public function start() {
        /**
         * Start routing. This should generally just be called once, and at the
         * bottom of the script.
         */
        $uri = $_GET['route'];
        foreach($this->routes as $url => $func) {
            $rgx = safe_rgx($url);
            preg_match($rgx, $uri, $matches);
            if(!empty($matches)) {
                call_user_func($func, $matches);
                return;
            }
        }
        print('404');
    }
}
$router = new Router();

/* VIEWS */

function index() {
    /**
     * The frontpage.
     */
    $recipes = Recipe::all(); 
    $ingredients = Ingredient::all();

    /* TODO: We should use a proper templating system, but for now this works ok. */
    include('templates/index.php');
}
$router->route('/', 'index');

function edit_recipe($r) {
    /**
     * The view for editing existing recipes, or creating new ones.
     */
    if(isset($r['id']))
        $recipe = Recipe::get($r['id']);
    else
        $recipe = NULL;


    if(!empty($_POST)) {
        if($recipe) {
            /* Update recipe */
            $recipe->update(array(
                'name' => $_POST['name'],
                'persons' => $_POST['persons'],
                'recipe' => $_POST['recipe'],
                'garniture' => $_POST['garniture']
            ));

            /* Delete all existing ingredients and timings for this recipe,
             * before we save new ones. This is _NOT_ optimal, but it gets
             * the work done.
             */
            foreach($recipe->ingredients() as $ingr) $ingr->delete();
            foreach($recipe->timings() as $time) $time->delete();
            
        } else {
            /* Create new recipe */ 
            $recipe = Recipe::create(array(
                'name' => $_POST['name'],
                'persons' => $_POST['persons'],
                'recipe' => $_POST['recipe'],
                'garniture' => $_POST['garniture']
            ));
        }

        for($i = 0 ; $i < count($_POST['timing_description']) ; $i++)
            Timing::create(array(
                'description' => $_POST['timing_description'][$i],
                'minutes'     => $_POST['timing_minutes'][$i],
                'recipe_id'   => $recipe->id,
            ));

        for($i = 0 ; $i < count($_POST['ingredient_name']) ; $i++)
            Ingredient::create(array(
                'name'      => $_POST['ingredient_name'][$i],
                'amount'    => $_POST['ingredient_amount'][$i],
                'recipe_id' => $recipe->id,
            ));
    }

    $timings = Timing::distinct('description');
    $ingredients = Ingredient::distinct('name');
    include('templates/edit_recipe.php');
}
$router->route('/recipe/new', 'edit_recipe');
$router->route('/recipe/(?<id>\d+)/edit', 'edit_recipe');

function recipe($r) {
    /**
     * The recipe details view.
     */
    $recipe = Recipe::get($r['id']);

    /* Update image orientation */
    if(!empty($_POST))
        $recipe->update(array(
            'image_orientation' => $_POST['orientation']
        ));

    /* Upload a new file */
    if(!empty($_FILES)) {
        $filename = sprintf('recipe-%s.%s', $recipe->id, explode($_FILES['file']['name'], '.')[-1]);
        move_uploaded_file($_FILES['file']['tmp_name'], 'static/images/'.$filename);
        $recipe->update(array(
            'image' => $filename,
        ));
    }

    include('templates/recipe.php');
}
$router->route('/recipe/(?<id>\d+)', 'recipe');

function delete_recipe($r) {
    /**
     * Delete a recipe.
     */
    $recipe = Recipe::get($r['id']);
    $recipe->delete();
    /* TODO: We should redirect at once - not using javascript. */
    include('templates/delete_recipe.php');
}
$router->route('/recipe/(?<id>\d+)/delete', 'delete_recipe');

/* Start routing... */
include('templates/header.php');
$router->start();
include('templates/footer.php');

/* Cleanup */
$db->close();
?>
