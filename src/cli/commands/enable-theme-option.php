<?php

use PomatioFramework\Pomatio_Framework_Disk;
use WP_CLI\ExitException;

if (defined('WP_CLI') && WP_CLI) {

    class Pomatio_Enable_Theme_Option_CLI_Command extends WP_CLI_Command {

        /**
         * Enable POM theme option
         *
         * ## OPTIONS
         *
         * [--settings_dir=<string>]
         * : Settings directory name.
         *
         * [--section=<string>]
         * : Name of the section to enable.
         *
         * @throws ExitException
         */
        public function __invoke($args, $assoc_args): void {
            Pomatio_Framework_Disk::create_settings_dir($assoc_args['settings_dir']);

            /**
             * Save the state of the setting.
             * 1 = Enabled
             * 0 = Disabled
             */
            $settings_array = array_filter((array)Pomatio_Framework_Disk::read_file('enabled_settings.php', $assoc_args['settings_dir'], 'array'));

            if (isset($settings_array[$assoc_args['section']])) {
                WP_CLI::error('The setting is already defined.');
            }

            $settings_array[$assoc_args['section']] = '1';

            $settings_content = (new Pomatio_Framework_Disk)->generate_file_content($settings_array, "Enabled settings array file.");
            $settings_path = (new Pomatio_Framework_Disk())->get_settings_path($assoc_args['settings_dir']);
            file_put_contents($settings_path . 'enabled_settings.php', $settings_content, LOCK_EX);

            WP_CLI::success("{$assoc_args['section']} setting enabled.");
        }

    }

    WP_CLI::add_command('pomatio enable_theme_option', 'Pomatio_Enable_Theme_Option_CLI_Command');

}
