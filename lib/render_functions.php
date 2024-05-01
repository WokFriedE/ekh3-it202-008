<?php

function render_input($data = array())
{
    include(__dir__ . "/../partials/input_field.php");
}

function render_button($data = array())
{
    include(__DIR__ . "/../partials/button.php");
}

function render_table($data = array())
{
    include(__DIR__ . "/../partials/table.php");
}

function render_game_card($game = array())
{
    include(__DIR__ . "/../partials/game_card.php");
}

function association_game_card($game = array())
{
    include(__DIR__ . "/../partials/association_game_card.php");
}
