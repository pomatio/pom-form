<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit069df07d080afa9eb5a1557f64508fe7
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'POM\\Form\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'POM\\Form\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'csstidy' => __DIR__ . '/..' . '/cerdic/css-tidy/class.csstidy.php',
        'csstidy_optimise' => __DIR__ . '/..' . '/cerdic/css-tidy/class.csstidy_optimise.php',
        'csstidy_print' => __DIR__ . '/..' . '/cerdic/css-tidy/class.csstidy_print.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit069df07d080afa9eb5a1557f64508fe7::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit069df07d080afa9eb5a1557f64508fe7::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit069df07d080afa9eb5a1557f64508fe7::$classMap;

        }, null, ClassLoader::class);
    }
}