<div id="actions">

    <script type="text/javascript">
        INGREDIENTS = [
        <?php foreach($ingredients as $ingr) { print("'".$ingr->name."',"); } ?>
        ];
        function activate_autocomplete() {
            $("input.ingredient").autocomplete({
                source: INGREDIENTS,
                select: function(event, ui) {
                    $(this).after("<input type=\"text\" class=\"ingredient\" /><br />");
                    activate_autocomplete();
                    update_recipe_list();
                }
            });
        }
        function update_recipe_list() {
            var time_below = $("input[name='time_below']").val();
            var ingredients = [];
            $('input.ingredient').each(function() {
                if($.trim($(this).val()) != '') {
                    ingredients.push($(this).val());
                }
            });

            $("a.recipe").each(function() {
                var show = true;
                if(time_below != '') {
                    if(parseInt($(this).attr('data-total-time')) < time_below) {
                        show = true;
                    } else {
                        show = false;
                    }
                }

                for(var i in ingredients) {
                    if($(this).attr('data-ingredients').indexOf('|'+ingredients[i]+'|') == -1)
                        show = false;
                }

                if(show) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        $(function() {
            $("input[name='time_below']").on('keyup', update_recipe_list);
            $("#actions").on('blur', 'input', function() {
                var empties = $('input.ingredient').filter(function() { return $(this).val() == '' }).slice(1);
                empties.remove();
                update_recipe_list();
            });
            activate_autocomplete();
        });
    </script>

    <h1>Filter</h1>

    <h3>Tid</h3>
    Under <input type="text" name="time_below" class="only-numbers" style="width: 30px;" maxlength="3" /> minutter<br />

    <h3>Ingredienser</h3>
    <input type="text" class="ingredient" /><br />

    <div id="navigation">
        <a href="/recipes/recipe/new">Tilføj</a>
    </div>
</div>

<div id="content">
    <?php foreach($recipes as $recipe) { ?>
    <a class="recipe <?php if($recipe->image_orientation) { print ($recipe->image_orientation); } else { print ('vertical'); } ?>" href="/recipes/recipe/<?php print($recipe->id); ?>"
        data-total-time="<?php print($recipe->total_time()); ?>"
        data-ingredients="<?php foreach($recipe->ingredients() as $ingr) { print('|'.$ingr->name); } ?>|"
        style="background-image: url(/recipes/static/images/<?php if($recipe->image) { print($recipe->image); } else { print('no-picture.png'); } ?>);"
    >
        <div class="name"><?php print($recipe->name); ?></div>
    </a>
    <?php } ?>
</div>
