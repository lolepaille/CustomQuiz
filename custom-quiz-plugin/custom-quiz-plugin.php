<?php
/*
Plugin Name: Custom Quiz Plugin
Description: A custom quiz plugin with an admin panel for updating questions and answers.
Version: 1.0
Author: ChatGPT
*/

// Admin Panel

function custom_quiz_admin_menu() {
    add_options_page('Custom Quiz Settings', 'Custom Quiz', 'manage_options', 'custom-quiz-settings', 'custom_quiz_settings_page');
}
add_action('admin_menu', 'custom_quiz_admin_menu');

function custom_quiz_settings_init() {
    register_setting('custom_quiz', 'custom_quiz_options');

    $sections = ['il' => 'IL Questions', 'ds' => 'DS Questions', 'dw' => 'DW Questions'];
    foreach ($sections as $key => $label) {
        add_settings_section($key, $label, null, 'custom_quiz');

        for ($i = 1; $i <= 4; $i++) {
            add_settings_field("{$key}_q{$i}", "Question $i", 'custom_quiz_question_field', 'custom_quiz', $key, [
                'label_for' => "{$key}_q{$i}",
                'name' => "{$key}_q{$i}",
                'type' => 'question'
            ]);
            add_settings_field("{$key}_a{$i}_1", "Answer 1 for Question $i", 'custom_quiz_question_field', 'custom_quiz', $key, [
                'label_for' => "{$key}_a{$i}_1",
                'name' => "{$key}_a{$i}_1",
                'type' => 'answer'
            ]);
            add_settings_field("{$key}_a{$i}_2", "Answer 2 for Question $i", 'custom_quiz_question_field', 'custom_quiz', $key, [
                'label_for' => "{$key}_a{$i}_2",
                'name' => "{$key}_a{$i}_2",
                'type' => 'answer'
            ]);
        }
    }
}
add_action('admin_init', 'custom_quiz_settings_init');

function custom_quiz_question_field($args) {
    $options = get_option('custom_quiz_options');
    $value = $options[$args['name']] ?? '';
    echo "<input type='text' name='custom_quiz_options[{$args['name']}]' value='{$value}' class='regular-text'>";
}

function custom_quiz_settings_page() {
    ?>
    <div class="wrap">
        <h2>Custom Quiz Settings</h2>
        <form action="options.php" method="post">
            <?php
            settings_fields('custom_quiz');
            do_settings_sections('custom_quiz');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Frontend Display

// Frontend Display

function display_custom_quiz() {
    $options = get_option('custom_quiz_options');
    $error = '';

    // Check if questions and answers are set in the backend
    $isConfigured = true;
    $sections = ['il', 'ds', 'dw'];
    foreach ($sections as $section) {
        for ($i = 1; $i <= 4; $i++) {
            if (!isset($options["{$section}_q{$i}"]) || !isset($options["{$section}_a{$i}_1"]) || !isset($options["{$section}_a{$i}_2"])) {
                $isConfigured = false;
                break 2; // Break out of both loops
            }
        }
    }

    if (!$isConfigured) {
        echo 'Please configure before use.';
        return;
    }

    $currentQuestion = isset($_POST['current_question']) ? intval($_POST['current_question']) : 1;
    $sections = ['il', 'ds', 'dw'];
    $currentSection = isset($_POST['current_section']) ? $_POST['current_section'] : 'il';

    if (!session_id()) {
        session_start();
    }

    if (isset($_POST['submit_quiz'])) {
        $_SESSION["{$currentSection}_{$currentQuestion}"] = $_POST["{$currentSection}_{$currentQuestion}"];

        $currentQuestion++;

        if ($currentQuestion > 4) {
            $currentQuestion = 1;
            if ($currentSection == 'il') {
                $currentSection = 'ds';
            } elseif ($currentSection == 'ds') {
                $currentSection = 'dw';
            } else {
                $x = 0;
                $y = 0;
                $z = 0;

                for ($i = 1; $i <= 4; $i++) {
                    $x += intval($_SESSION["il_$i"]);
                    $y += intval($_SESSION["ds_$i"]);
                    $z += intval($_SESSION["dw_$i"]);
                }

                if (($x == 1 || $x == 2) && ($y == 1 || $y == 2) && ($z == 1 || $z == 2)) {
                    echo 'You belong to the first category.';
                } elseif (($x == 3 || $x == 4) && ($y == 1 || $y == 2) && ($z == 1 || $z == 2)) {
                    echo 'You belong to the second category.';
                } elseif (($y == 3 || $y == 4) && ($x == 1 || $x == 2) && ($z == 1 || $z == 2)) {
                    echo 'You belong to the third category.';
                } elseif (($z == 3 || $z == 4) && ($x == 1 || $x == 2) && ($y == 1 || $y == 2)) {
                    echo 'You belong to the fourth category.';
                } elseif (($x == 3 || $x == 4) && ($y == 3 || $y == 4) && ($z == 3 || $z == 4)) {
                    echo 'You belong to the fifth category.';
                } else {
                    $error = 'Invalid selection.';
                }

                // Clear session data after displaying results
                foreach ($_SESSION as $key => $value) {
                    unset($_SESSION[$key]);
                }
                return;
            }
        }
    }

    if ($error) {
        echo '<div class="error">' . $error . '</div>';
    } else {
        // Display the current question
        echo '<form method="post">';
        echo "<input type='hidden' name='current_question' value='{$currentQuestion}'>";
        echo "<input type='hidden' name='current_section' value='{$currentSection}'>";
        echo $options["{$currentSection}_q{$currentQuestion}"] . ": ";
        echo "<input type='radio' name='{$currentSection}_{$currentQuestion}' value='1' required> " . $options["{$currentSection}_a{$currentQuestion}_1"];
        echo " <input type='radio' name='{$currentSection}_{$currentQuestion}' value='0'> " . $options["{$currentSection}_a{$currentQuestion}_2"] . "<br>";
        echo '<br><input type="submit" name="submit_quiz" value="Next">';
        echo '</form>';
    }
}
add_shortcode('custom_quiz', 'display_custom_quiz');

?>