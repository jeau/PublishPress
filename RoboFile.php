<?php

use Robo\Exception\TaskExitException;


/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    const SOURCE_PATH = 'src';

    const PACKAGE_PATH = 'packages';

    const PLUGIN_SLUG = 'publishpress';

    const PROPERTIES_FILE_PATH = __DIR__ . '/.properties.ini';

    protected $options = array();

    protected function getProperties()
    {
        if (empty($this->options)) {
            if (!file_exists(self::PROPERTIES_FILE_PATH)) {
                throw new Exception("Local properties file (.properties.ini) not found", 1);
            }

            $this->options = parse_ini_file(self::PROPERTIES_FILE_PATH);
        }

        return $this->options;
    }

    /**
     * Get the current version of the plugin
     */
    protected function getVersion()
    {
        $file = file_get_contents(self::SOURCE_PATH . '/' .  self::PLUGIN_SLUG . '.php');

        preg_match('/Version:\s*([0-9\.a-z]*)/i', $file, $matches);

        return $matches[1];
    }

    /**
     * Register a change on the changelog for the current version
     */
    public function changelog()
    {
        $version = $this->getVersion();

        return $this->taskChangelog()
            ->version($version)
            ->askForChanges()
            ->run();
    }

    /**
     * Build the ZIP package
     *
     * @param string $destination Destination for the package. The ZIP file will be moved to that path.
     */
    public function packBuild($destination = null)
    {
        $this->say('Building the package');

        // Build the package
        $filename = self::PLUGIN_SLUG . '.zip';
        $packPath = self::PACKAGE_PATH . '/'. $filename;
        $pack     = $this->taskPack($packPath);

        // Remove existent package
        if (file_exists($packPath)) {
            unlink($packPath);
        }

        $srcContent = scandir(self::SOURCE_PATH);
        foreach ($srcContent as $content) {
            $ignore = array(
                '.',
                '..',
                'build',
                'tests',
                '.git',
                '.gitignore',
                'README',
                '.DS_Store',
            );

            if (! in_array($content, $ignore)) {
                $path = self::SOURCE_PATH . '/' . $content;

                if (is_file($path)) {
                    $pack->addFile($content, $path);
                } else {
                    $pack->addDir($content, $path);
                }
            }
        }

        $return = $pack->run();

        // Should we move to any specific destination?
        if (!is_null($destination)) {
            if (!realpath($destination)) {
                throw new RuntimeException('Invalid destination path');
            }

            $destFile = realpath($destination) . '/' . $filename;

            $this->say('Moving the new package to ' . $destFile);

            rename(self::PACKAGE_PATH . '/' . $filename, $destFile);
        }

        $this->say("Package built successfully");

        return $return;
    }

    /**
     * Copy the folder to the wordpress given location
     *
     * @param string $wordPressPath Path for the WordPress installation
     */
    public function packInstall($wordPressPath)
    {
        $this->say('Building the package');

        // Build the package
        $packPath = realpath(self::PACKAGE_PATH) . '/'. self::PLUGIN_SLUG;

        if (is_dir($packPath)) {
            $this->_exec('rm -rf ' . $packPath);
        }

        $this->packBuild();

        // Unzip it
        $this->_exec('unzip ' . $packPath . '.zip -d ' . $packPath);

        // Installing the package
        $this->say('Installing the package');

        if (!realpath($wordPressPath)) {
            throw new RuntimeException('Invalid WordPress path');
        }

        $dest = realpath($wordPressPath) . '/wp-content/plugins/' . self::PLUGIN_SLUG;
        // Remove existent plugin directory
        if (is_dir($dest)) {
            $this->_exec('rm -rf ' . $dest);
        }

        $this->_exec('mv ' . $packPath . ' ' . $dest);

        $this->say('Package installed');
        $this->_exec('say "pack installed!"');

        return;
    }

    /**
     * Watch for changes and copy the folder to the wordpress given location
     *
     * @param string $wordPressPath Path for the WordPress installation
     */
    public function packWatchInstall($wordPressPath)
    {
        $return = $this->taskWatch()
            ->monitor('src', function() use ($wordPressPath) {
                $this->packInstall($wordPressPath);
            })
            ->run();

        return $return;
    }

    /**
     * Return a list of PO files from the languages dir
     *
     * @return string
     */
    protected function getPoFiles()
    {
        $languageDir = 'src/languages';

        return glob($languageDir . '/*.po');
    }

    /**
     * Compile language MO files from PO files.
     *
     * @param string $poFile
     * @return Result
     */
    protected function compileMOFromPO($poFile)
    {
        $moFile = str_replace('.po', '.mo', $poFile);

        return $this->_exec('msgfmt --output-file=' . $moFile . ' ' . $poFile);
    }

    /**
     * Compile all PO language files
     */
    public function langCompile()
    {
        $return = null;
        $files  = $this->getPoFiles();

        foreach ($files as $file) {
            $return = $this->compileMOFromPO($file);

            $this->say('Language file compiled');
        }

        return $return;
    }

    /**
     * Watch language files and compile the changed ones to MO files.
     */
    public function langWatch()
    {
        $return = null;
        $task   = $this->taskWatch();
        $files  = $this->getPoFiles();

        foreach ($files as $file) {
            $task->monitor($file, function() use ($file) {
                $return = $this->compileMOFromPO($file);

                $this->say('Language file compiled');
            });
        }

        $task->run();

        return $return;
    }

    /**
     * Sync WP files with src files
     */
    public function syncWp()
    {
        $return = $this->_exec('sh ./sync-wp.sh');

        return $return;
    }

    /**
     * Sync src files with WP files
     */
    public function syncSrc()
    {
        $return = $this->_exec('sh ./sync-src.sh');

        return $return;
    }

    public function syncSvnTag($tag)
    {
        $properties = $this->getProperties();

        $this->_exec('cd ' . $properties['svn_path'] . ' && svn update');
        $this->_exec('cd ' . $properties['svn_path'] . ' && cp -r ' . realpath(__DIR__) . '/' . self::SOURCE_PATH . '/* ' . $properties['svn_path'] . '/trunk');
        $this->_exec('cd ' . $properties['svn_path'] . ' && svn cp trunk tags/' . $tag);
        $return = $this->_exec('cd ' . $properties['svn_path'] . ' && svn commit -m "Tagging ' . $tag . '"');

        return $return;
    }

    /**
     * Watch src files and sync with wordpress
     */
    public function reactCompile()
    {
        $return = $this->_exec('./node_modules/.bin/babel src/modules/efmigration/lib/babel -d src/modules/efmigration/lib/js --presets es2015 --presets react');

        return $return;
    }
}
