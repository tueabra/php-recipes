<div id="actions">
    <div id="navigation">
        <?php if($recipe) { ?>
            <a href="/recipes/recipe/<?php print($recipe->id); ?>">Tilbage</a>
        <?php } else { ?>
            <a href="/recipes/">Tilbage</a>
        <?php } ?>
        <a href="/recipes/recipe/<?php print($recipe->id); ?>/delete" onclick="return confirm('Vil du virkelig slette denne opskrift?');">Slet</a>
    </div>
</div>

<div id="content">

    <script type="text/javascript">
        INGREDIENTS = [
        <?php foreach($ingredients as $ingr) { print("'".$ingr."',"); } ?>
        ];
        TIMINGS = [
        <?php foreach($timings as $time) { print("'".$time."',"); } ?>
        ];
        function update_autocomplete() {
            $("input[name='ingredient_name[]']").autocomplete({
                source: INGREDIENTS
            });
            $("input[name='timing_description[]']").autocomplete({
                source: TIMINGS
            });
        }
        $(function() {
            $('a.add-element').on('click', function() {
                var inputcell = $(this).closest('th').next('td');
                var template = inputcell.find('div').first().clone();
                template.find("input[type='hidden']").remove();
                template.find("input").val('').attr('value', '');
                inputcell.append(template);
                update_autocomplete();
            });
            update_autocomplete();
        });
    </script>

    <?php if($recipe) { ?>
        <h1>Redigér <?php print($recipe->name); ?></h1>
    <?php } else { ?>
        <h1>Ny opskrift</h1>
    <?php } ?>

    <form method="POST" action="">

    <table class="invisible">

        <tr>
            <th>Navn</th>
            <td><input type="text" name="name" value="<?php print($recipe->name); ?>" /></td>
        </tr>

        <tr>
            <th>Antal personer</th>
            <td><input type="text" name="persons" class="only-numbers" value="<?php print($recipe->persons); ?>" /><td>
        </tr>

        <tr>
            <th>Tid [<a href="#" class="add-element">tilføj</a>]</th>
            <td>
        <?php if($recipe && $recipe->timings()) { ?>
            <?php foreach($recipe->timings() as $time) { ?>
                <div>
                    <input type="hidden" name="timing_id[]" value="<?php print($time->id) ; ?>" />
                    <input type="text" name="timing_description[]" value="<?php print($time->description) ; ?>" />:
                    <input type="text" name="timing_minutes[]" class="only-numbers" maxlength="3" style="width: 30px;" value="<?php print($time->minutes) ; ?>" /> minutter<br />
                </div>
            <?php } ?>
        <?php } else { ?>
                <div>
                    <input type="text" name="timing_description[]" />:
                    <input type="text" name="timing_minutes[]" class="only-numbers" maxlength="3" style="width: 30px;" /> minutter
                </div>
        <?php } ?>
            </td>
        </tr>

        <tr>
            <th>Ingredienser [<a href="#" class="add-element">tilføj</a>]</th>
            <td>
        <?php if($recipe && $recipe->ingredients()) { ?>
            <?php foreach($recipe->ingredients() as $ingr) { ?>
                <div>
                    <input type="hidden" name="ingredient_id[]" value="<?php print($ingr->id); ?>" />
                    Mængde <input type="text" name="ingredient_amount[]" style="width: 70px;" value="<?php print($ingr->amount); ?>" />
                    af <input type="text" name="ingredient_name[]" value="<?php print($ingr->name); ?>" />
                </div>
            <?php } ?>
        <?php } else { ?>
                <div>
                    Mængde <input type="text" name="ingredient_amount[]" style="width: 70px;" />
                    af <input type="text" name="ingredient_name[]" />
                </div>
        <?php } ?>
            </td>
        </tr>

        <tr>
            <th>Opskrift</th>
            <td><textarea cols="50" rows="7" name="recipe"><?php print($recipe->recipe); ?></textarea></td>
        </tr>

        <tr>
            <th>Tilbehør</th>
            <td><textarea cols="50" rows="3" name="garniture"><?php print($recipe->garniture); ?></textarea></td>
        </tr>

        <tr>
            <th>&nbsp;</th>
            <td><input type="submit" value="Gem" /></td>
        </tr>
    </table>

    </form>

</div>
