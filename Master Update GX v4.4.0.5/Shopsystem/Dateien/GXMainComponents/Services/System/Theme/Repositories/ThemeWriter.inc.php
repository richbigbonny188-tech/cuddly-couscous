<?php
/*--------------------------------------------------------------------------------------------------
    ThemeWriter.inc.php 2020-09-02
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

use Gambio\GX\Services\System\ThemeSettings\Factories\ThemeSettingsDataFactory;
use Gambio\StyleEdit\Core\Repositories\Entities\ConfigurationCollection;
use League\Flysystem\FileExistsException;

/**
 * Class ThemeWriter
 * publish theme and create cache and compile template 5.55 s
 * publish theme and compile template 3.62 s
 * compile template 2.63, 2.78, 2.69,
 * normal 0.676s
 *
 */
class ThemeWriter implements ThemeWriterInterface
{
    protected const FILE_IS_SCRIPT_OR_SHEET_PATTERN = '#\.(css|js)$#i';
    protected const HTML_SYSTEM_DIRECTORY           = 'html/system';
    protected const JS_SYSTEM_DIRECTORY           = 'javascripts/system';
    protected const SETTINGS_FILENAME               = 'settings.json';

    protected const UNMINIFIED_FILE_NAME_PATTERN = '#(\.min)?\.(css|js)$#i';

    /**
     * @var FilesystemAdapter
     */
    protected $filesystem;
    /**
     * @var string
     */
    protected $shopSource;
    /**
     * @var ThemeId
     */
    protected $sourceThemeId;

    /**
     * @var array
     */
    protected   $themeExtensionFiles;
    /**
     * @var ThemeSettingsDataFactory
     */
    private $themeSettingsFactory;


    /**
     * ThemeWriter constructor.
     *
     * @param FilesystemAdapter        $filesystem
     * @param ExistingDirectory        $shopSource
     * @param array                    $themeExtensionFiles
     * @param ThemeSettingsDataFactory $themeSettingsFactory
     */
    public function __construct(FilesystemAdapter $filesystem, ExistingDirectory $shopSource, array $themeExtensionFiles, ThemeSettingsDataFactory $themeSettingsFactory)
    {
        $this->filesystem = $filesystem;
        $this->shopSource = $shopSource->getAbsolutePath();
        $this->themeExtensionFiles = $themeExtensionFiles;
        $this->themeSettingsFactory = $themeSettingsFactory;
    }


    /**
     * Saves the given them to the destination.
     *
     * @param ThemeInterface              $theme       Theme to be saved.
     * @param ThemeDirectoryRootInterface $destination Destination directory.
     *
     * @throws Exception
     */
    public function save(ThemeInterface $theme, ThemeDirectoryRootInterface $destination): void
    {
        $this->sourceThemeId = $theme->getId();

        $destinationDirectory = $this->_getRelative($destination);
        $contents             = $this->filesystem->listContents($destinationDirectory);

        // delete all files and directories in the public/theme folder except for the .gitkeep, if it exists
        foreach ($contents as $content) {
            $path = '/' . $content['path'];
            if ($content['type'] === 'dir') {
                $this->filesystem->deleteDir($path);
            } elseif ($path !== $destinationDirectory . '/.gitkeep') {
                $this->filesystem->delete('/' . $content['path']);
            }
        }

        @$this->filesystem->createDir($destinationDirectory, ['visibility' => 'public']);

        $childThemes = [];

        while ($theme->hasParent()) {
            $childThemes[] = $theme;
            $theme         = $theme->getParentTheme();
        }
        // at this point, $theme is the main theme and $childThemes contains all child themes, reversed.

        $childThemes = array_reverse($childThemes);
        $this->_saveThemeStructure($theme->toMainTheme(), $destination, ...$childThemes);
        $this->_findFilesThatHaveNoMatchingMinOrSourceFile($destination);
        @$this->recursiveChmod($destination->getPath());
    }


    /**
     * @param ThemeDirectoryRootInterface $directory
     *
     * @return mixed
     */
    protected function _getRelative(ThemeDirectoryRootInterface $directory)
    {
        return preg_replace('#' .preg_quote($this->shopSource,'#'). '#', '', $directory->getPath(),1);
    }


    /**
     * @param MainThemeInterface          $mainTheme
     * @param ThemeDirectoryRootInterface $destination
     * @param ThemeInterface              ...$childThemes
     *
     * @throws FileNotFoundException
     * @throws Exception
     */
    protected function _saveThemeStructure(
        MainThemeInterface $mainTheme,
        ThemeDirectoryRootInterface $destination,
        ThemeInterface ...$childThemes
    ): void {

        $this->_saveMainTheme($mainTheme, $destination);
        $activeTheme = count($childThemes) > 0 ? end($childThemes) : $mainTheme;
        $this->_copySettingsJson($activeTheme, $destination);
        $this->_copyThemeJson($activeTheme, $destination);
        reset($childThemes);
        $activeVariants = $this->_loadActiveVariants($destination);

        $whitelist = CustomThemeCopyResponse::create();
        /**
         * System GXModules are created based on the default theme structure,
         * they can't predict all the variants changes
         */
        $whitelist->append($this->_copyGxModulesExtensionsForTheme('all', $destination));
        $whitelist->append($this->_copyGxModulesExtensionsForTheme($mainTheme->getId()->getId(), $destination));
        if (count($childThemes) === 0) {
            $this->_applyActiveThemeVariants($mainTheme, $destination, $activeVariants);
        }


        foreach ($childThemes as $childTheme) {
            $result = $this->_saveTheme($childTheme, $destination);
            $themeOverloadInfo = CustomThemeCopyResponse::create();
            $themeOverloadInfo->append($result);
            $themeOverloadInfo->appendWhitelist($whitelist);
            $whitelist->appendWhitelist($result);
            /**
             * System GXModules are created based on the default theme structure,
             * they can't predict all the variants changes
             */
            $whitelist->appendWhitelist(
                $this->_copyGxModulesExtensionsForTheme($childTheme->getId()->getId(), $destination)
            );

            //apply variants
            if ($childTheme->getId()->equals($activeTheme->getId())) {
                $themeOverloadInfo->append(
                    $this->_applyActiveThemeVariants($mainTheme, $destination, $activeVariants)
                );
            }
            //apply GxModules core files

            //apply overloads
            $whitelist->appendWhitelist(
                $this->_applyHtmlOverloads($themeOverloadInfo, $destination, $childTheme->getId())
            );
        }

        /**
         * Custom area applied at the end, they can change everything in the theme
         */
        $this->_applyGxModulesCustomOverloads($mainTheme, $childThemes, $destination);
    }


    /**
     * Checks the $themeDirectoryRoot (/public/theme)
     * for files that have no matching source or .min file
     *
     * @param ThemeDirectoryRootInterface $themeDirectoryRoot
     */
    protected function _findFilesThatHaveNoMatchingMinOrSourceFile(ThemeDirectoryRootInterface $themeDirectoryRoot): void
    {
        $files = $this->_findScriptsAndSheetsInDirectory(new StringType($themeDirectoryRoot->getPath()));

        $matchedFiles = [];

        // creating an two dimensional array matching .min files with source files
        foreach ($files as $file) {

            $notMinifiedFileName = preg_replace(self::UNMINIFIED_FILE_NAME_PATTERN, '.$2', $file);

            if (!isset($matchedFiles[$notMinifiedFileName])) {

                $matchedFiles[$notMinifiedFileName] = [];
            }

            $matchedFiles[$notMinifiedFileName][] = $file;
        }

        // detecting files that have no min or source file
        $singularFiles = array_filter($matchedFiles,
            static function ($item) {

                return count($item) === 1 ? array_shift($item) : null;
            });

        //  creating a one dimensional none associative array
        $singularFiles = array_values(array_map('array_shift', $singularFiles));

        $this->_duplicateFilesThatHaveNoMatchingMinOrSourceFile($singularFiles);
    }


    /**
     * @param MainThemeInterface          $mainTheme
     * @param ThemeDirectoryRootInterface $destination
     *
     * @return void
     */
    protected function _saveMainTheme(MainThemeInterface $mainTheme, ThemeDirectoryRootInterface $destination) : void
    {
        $this->_copyMainThemeDirectory($mainTheme->getId(), $mainTheme->getConfig(), $destination);
        if ($mainTheme->getVariants()) {
            $this->_copyMainThemeDirectory($mainTheme->getId(), $mainTheme->getVariants(), $destination);
        }
        $this->_copyMainThemeDirectory($mainTheme->getId(), $mainTheme->getFonts(), $destination);
        $this->_copyMainThemeDirectory($mainTheme->getId(), $mainTheme->getHtml(), $destination);
        $this->_copyMainThemeDirectory($mainTheme->getId(), $mainTheme->getImages(), $destination);
        if ($mainTheme->getJs()) {
            $this->_copyMainThemeDirectory($mainTheme->getId(), $mainTheme->getJs(), $destination);
        }
        $this->_copyMainThemeDirectory($mainTheme->getId(), $mainTheme->getStyles(), $destination);
        $this->_copyMainThemeStyleEditDirectory($mainTheme->getId(), $mainTheme->getStyleEdit(), $destination);
    }


    /**
     * @param VariableThemeDirectoriesInterface|IdentifiedThemeInterface $theme
     * @param ThemeDirectoryRootInterface       $destination
     */
    protected function _copySettingsJson(
        $theme,
        ThemeDirectoryRootInterface $destination
    ): void {

        $settingsFile = $this->themeSettingsFactory->createForTheme($theme->getId()->getId());
        $jsonDestination = $this->_getRelative($destination) . DIRECTORY_SEPARATOR . static::SETTINGS_FILENAME;

        if ($settingsFile !== null) {
            $jsonSource      = $this->_getRelativeFromFile($settingsFile->filename());
            $this->_copyOrReplaceFile($jsonSource, $jsonDestination);
        } else {
            $this->filesystem->put($jsonDestination, '[]');
        }
    }


    /**
     * @param VariableThemeDirectoriesInterface $theme
     * @param ThemeDirectoryRootInterface       $destination
     *
     * @throws Exception
     */
    protected function _copyThemeJson(
        VariableThemeDirectoriesInterface $theme,
        ThemeDirectoryRootInterface $destination
    ): void {
        $jsonSource      = $this->_getRelative($theme->getConfig()->getRoot()). '/../theme.json';
        $jsonDestination = $this->_getRelative($destination) . '/theme.json';
        $this->_copyOrReplaceFile($jsonSource, $jsonDestination);
    }


    /**
     * @param IdentifiedThemeInterface    $theme
     * @param ThemeDirectoryRootInterface $destination
     *
     * @param ConfigurationCollection     $variants
     *
     * @return bool|CustomThemeCopyResponse
     * @throws FileNotFoundException
     */
    protected function _applyActiveThemeVariants(
        IdentifiedThemeInterface $theme,
        ThemeDirectoryRootInterface $destination,
        ConfigurationCollection $variants
    ) {
        $result   = CustomThemeCopyResponse::create();
        foreach ($variants as $selectedVariant) {
            $variantPath = 'variants/' . $selectedVariant->id().'/'.$selectedVariant->value()->id;
            if ($destination->hasPath($variantPath)) {

                $variant = VariantDirectories::createWithCustomPrefix($destination->withPath($variantPath),
                    $theme->getId());
                $result->appendWhitelist($this->_copyBaseThemeFiles($variant, $destination));
            }
        }

        return $result;
    }


    /**
     * @param ThemeInterface              $theme
     * @param ThemeDirectoryRootInterface $destination
     *
     * @return bool|CustomThemeCopyResponse|mixed
     * @throws FileNotFoundException
     */
    protected function _saveTheme(ThemeInterface $theme, ThemeDirectoryRootInterface $destination)
    {
        $this->_copyThemeDirectory($theme->getId(), $theme->getConfig(), $destination);

        $result = $this->_copyBaseThemeFiles($theme, $destination);

        if ($theme->getVariants()) {
            $this->_copyThemeDirectory($theme->getId(), $theme->getVariants(), $destination);
        }

        if($theme->getStyleEdit() !== null) {
            $this->_copyStyleEditThemeDirectory($theme->getId(), $theme->getStyleEdit(), $destination);
        }
        return $result;
    }


    /**
     * @param CustomThemeCopyResponse $overloaders
     * @param ThemeDirectoryRootInterface $destination
     *
     * @param ThemeId $themeId
     * @return bool|CustomThemeCopyResponse
     * @throws FileNotFoundException
     */
    protected function _applyHtmlOverloads(CustomThemeCopyResponse $overloaders, ThemeDirectoryRootInterface $destination, ThemeId $themeId)
    {
        if (count($overloaders->getOverloaders())) {

            $destinationPath = $this->_getRelative($destination->withPath(static::HTML_SYSTEM_DIRECTORY));
            $htmlPrefix      = '0_global_extender_'.$themeId->getId();
            //save theme overload file
            $htmlThemeOverloadFilename = $htmlPrefix . '.html';
            $htmlThemeOverloadFilePath = $destinationPath . DIRECTORY_SEPARATOR . $htmlThemeOverloadFilename;
            $htmlThemeOverloadContent  = '';

            foreach ($overloaders->getOverloaders() as $existingFile) {
                $resource                 = $this->filesystem->readStream($this->_getRelativeFromFile($existingFile));
                $htmlThemeOverloadContent .= PHP_EOL . stream_get_contents($resource);
            }

            $putStream = tmpfile();
            fwrite($putStream, $htmlThemeOverloadContent);
            rewind($putStream);
            $this->filesystem->putStream($htmlThemeOverloadFilePath, $putStream);

            //create temporary file to be use as model to create the final overloads
            $htmlTempFilename = $htmlPrefix . '_temp.html';
            $htmlTempFilePath = $destinationPath . DIRECTORY_SEPARATOR . $htmlTempFilename;
            $htmlTempContent  = '{include file="get_usermod:{$tpl_path}' . $htmlThemeOverloadFilename . '"}';

            $putStream = tmpfile();
            fwrite($putStream, $htmlTempContent);
            rewind($putStream);
            $this->filesystem->putStream($htmlTempFilePath, $putStream);

            //create whitelist
            $whitelist   = array_map(function (ExistingFile $file) {
                return $this->_getRelativeFromFile($file);
            },
                $overloaders->getWhiteList()->getArray());
            $whitelist[] = $htmlThemeOverloadFilePath;
            $whitelist[] = $htmlTempFilePath;
            $newWhiteList = [];

            //extends all the files
            $path                 = str_replace($this->_getRelative($destination), '', $destinationPath);
            $destinationDirectory = ThemeDirectory::create($destination->withPath($path));
            foreach ($destinationDirectory->getFiles() as $fileToBeOverloaded) {
                if (strpos($fileToBeOverloaded, 'content_zone_') !== 0) {
                    $filePath = $destinationPath . DIRECTORY_SEPARATOR . $fileToBeOverloaded;
                    if (!in_array($filePath, $whitelist, false)) {
                        $newWhiteList[] = $this->_extendHtmlFile(
                            $htmlTempFilePath,
                            $filePath,
                            pathinfo($htmlThemeOverloadFilename, PATHINFO_FILENAME)
                        );
                    }
                }
            }
            $this->filesystem->delete($htmlTempFilePath);
            $newWhiteList[] = new ExistingFile(new NonEmptyStringType($this->shopSource . DIRECTORY_SEPARATOR . $htmlThemeOverloadFilePath));

            return CustomThemeCopyResponse::createWithOverloadsAndWhitelist(new ExistingFileCollection([]),
                new ExistingFileCollection($newWhiteList));
        }
        return CustomThemeCopyResponse::create();
    }


    /**
     * @param StringType $directory
     *
     * @return string[]
     */
    protected function _findScriptsAndSheetsInDirectory(StringType $directory): array
    {
        $result = [];

        foreach (new DirectoryIterator($directory->asString()) as $file) {

            if (!$file->isDot()) {

                $fileName = new StringType($file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename());

                if ($file->isDir()) {

                    foreach ($this->_findScriptsAndSheetsInDirectory($fileName) as $recursivelyFoundFile) {

                        $result[] = $recursivelyFoundFile;
                    }
                } elseif ($file->isFile() && preg_match(self::FILE_IS_SCRIPT_OR_SHEET_PATTERN, $fileName->asString())) {

                    $result[] = $fileName->asString();
                }
            }
        }

        return $result;
    }


    /**
     * Creates a duplicate of a file with .min if only the source file exists
     * or a file without .min if only the .min file exists
     *
     * @param string[] $files
     */
    protected function _duplicateFilesThatHaveNoMatchingMinOrSourceFile(array $files): void
    {
        foreach ($files as $file) {

            if (preg_match(self::UNMINIFIED_FILE_NAME_PATTERN, $file, $result)) {

                //  file only exists as source file
                if ($result[1] === '') {

                    $destination = preg_replace(self::UNMINIFIED_FILE_NAME_PATTERN, '.min.$2', $file);
                } //  File only exists as .min file
                else {

                    $destination = preg_replace(self::UNMINIFIED_FILE_NAME_PATTERN, '.$2', $file);
                }

                copy($file, $destination);
            }
        }
    }


    /**
     * @param ThemeId                     $themeId
     * @param ThemeDirectoryInterface     $directory
     * @param ThemeDirectoryRootInterface $destination
     */
    protected function _copyMainThemeDirectory(
        ThemeId $themeId,
        ThemeDirectoryInterface $directory,
        ThemeDirectoryRootInterface $destination
    ): void {
        $childDirectories = $directory->getChildren();

        if ($childDirectories) {
            foreach ($childDirectories as $childDir) {
                $this->_copyMainThemeDirectory($themeId, $childDir, $destination);
            }
        }

        foreach ($directory->getFiles() as $file) {
            $this->_copyMainThemeFile($themeId, $directory, $destination, $file);
        }
    }


    /**
     * @param ThemeId                     $themeId
     * @param ThemeDirectoryInterface     $directory
     * @param ThemeDirectoryRootInterface $destination
     */
    protected function _copyMainThemeStyleEditDirectory(
        ThemeId $themeId,
        ThemeDirectoryInterface $directory,
        ThemeDirectoryRootInterface $destination
    ): void {
        $childDirectories = $directory->getChildren();

        if ($childDirectories) {
            foreach ($childDirectories as $childDir) {
                $this->_copyMainThemeDirectory($themeId, $childDir, $destination);
            }
        }

        foreach ($directory->getFiles() as $file) {
            // ignores files that are created by styleedit
            if (!$themeId->equals($this->sourceThemeId)
                && $this->_endsWith($file, '.json')
                && $this->_endsWith($directory->getRoot()->getPath(), 'styleedit')) {
                continue;
            }
            $this->_copyMainThemeFile($themeId, $directory, $destination, $file);
        }

    }

    /**
     * @param ThemeDirectoryRootInterface $destination
     *
     * @return ConfigurationCollection
     * @throws Exception
     */
    protected function _loadActiveVariants(ThemeDirectoryRootInterface $destination): ConfigurationCollection
    {

        $settingsFilename = $this->_getRelative($destination) . DIRECTORY_SEPARATOR . static::SETTINGS_FILENAME;
        if ($this->filesystem->has($settingsFilename)) {
            $settingsContent = stream_get_contents($this->filesystem->readStream($settingsFilename));
            $configurations  = json_decode($settingsContent, false);
        } else {
            $configurations = [];
        }
        $activeVariants = [];
        foreach ($configurations as $configuration) {
            if ($configuration->type === 'variant') {
                $activeVariants[] = $configuration;
            }
        }

        return ConfigurationCollection::createFromJsonList($activeVariants);
    }


    /**
     * @param IdentifiedThemeInterface $baseTheme
     * @param ThemeDirectoryRootInterface $destination
     * @return CustomThemeCopyResponse
     * @throws FileNotFoundException
     */
    protected function _copyBaseThemeFiles(IdentifiedThemeInterface $baseTheme, ThemeDirectoryRootInterface $destination): CustomThemeCopyResponse
    {
        $result = CustomThemeCopyResponse::create();
        if ($baseTheme->getHtml() !== null) {
            $this->_copyThemeDirectory($baseTheme->getId(), $baseTheme->getHtml(), $destination);
        }
        if ($baseTheme->getCustomHtml() !== null) {
            $result->append($this->_copyCustomHtmlDirectory($baseTheme, $baseTheme->getCustomHtml(), $destination));
        }

        if ($baseTheme->getJs() !== null) {
            $this->_copyThemeDirectory($baseTheme->getId(), $baseTheme->getJs(), $destination);
        }

        if ($baseTheme->getCustomJs() !== null) {
            $this->_copyCustomJsDirectory($baseTheme->getId(), $baseTheme->getCustomJs(), $destination);
        }

        if ($baseTheme->getFonts() !== null) {
            $this->_copyThemeFontsDirectory($baseTheme->getId(), $baseTheme->getFonts(), $destination);
        }

        if ($baseTheme->getStyles() !== null) {
            $this->_copyThemeDirectory($baseTheme->getId(), $baseTheme->getStyles(), $destination);
        }

        if ($baseTheme->getCustomStyles() !== null) {
            $this->_copyCustomStylesDirectory($baseTheme->getId(), $baseTheme->getCustomStyles(), $destination);
        }

        if ($baseTheme->getImages() !== null) {
            $this->_copyThemeImagesDirectory($baseTheme->getId(), $baseTheme->getImages(), $destination);
        }

        if ($baseTheme->getJsExtensions() !== null) {
            $this->_copyThemeDirectory($baseTheme->getId(), $baseTheme->getJsExtensions(), $destination);
        }


        return $result;
    }

    /**
     * @param ThemeId                      $themeId
     * @param                              $directory
     * @param ThemeDirectoryRootInterface  $destination
     *
     * @throws FileNotFoundException
     */
    protected function _copyThemeDirectory(
        ThemeId $themeId,
        ThemeDirectoryInterface $directory,
        ThemeDirectoryRootInterface $destination
    ): void
    {
        $childDirectories = $directory->getChildren();
        if ($childDirectories) {
            foreach ($childDirectories as $childDirectory) {
                $this->_copyThemeDirectory($themeId, $childDirectory, $destination);
            }
        }

        foreach ($directory->getFiles() as $file) {
            $this->_copyOrReplaceThemeFile($themeId, $directory, $destination, $file);
        }
    }

    /**
     * @param IdentifiedThemeInterface    $theme
     * @param                             $directory
     * @param ThemeDirectoryRootInterface $destination
     *
     * @return bool|CustomThemeCopyResponse|mixed
     * @throws FileNotFoundException
     */
    protected function _copyCustomHtmlDirectory(
        IdentifiedThemeInterface $theme,
        ThemeDirectoryInterface $directory,
        ThemeDirectoryRootInterface $destination
    ) : CustomThemeCopyResponse
    {
        $overloadFilesList = [];
        $whitelist         = [];


        $childDirectories = $directory->getChildren();
        if ($childDirectories) {
            $innerWhitelist         = [];
            $innerOverloadFilesList = [];

            foreach ($childDirectories as $childDirectory) {
                $innerResult              = $this->_copyCustomHtmlDirectory($theme, $childDirectory, $destination);
                $innerWhitelist[]         = $innerResult->getWhiteList()->getArray();
                $innerOverloadFilesList[] = $innerResult->getOverloaders()->getArray();
            }
            $overloadFilesList = array_merge(...$innerOverloadFilesList);
            $whitelist         = array_merge(...$innerWhitelist);
        }

        [$sourcePath, $destinationPath] = $this->getPaths($theme->getId(), $directory, $destination);
        foreach ($directory->getFiles() as $file) {
            $sourceFile      = $sourcePath . DIRECTORY_SEPARATOR . $file;
            $destinationFile = $destinationPath . DIRECTORY_SEPARATOR . $file;

            if ($this->filesystem->has($destinationFile)) {
                $whitelist[] = $this->_extendHtmlFile($sourceFile,
                    $destinationFile,
                    $theme->getPrefix());
            } elseif ($theme instanceof VariantDirectoriesInterface) {
                //variants should copy files that is created
                $this->_copyFile($sourceFile, $destinationFile);
                $whitelist[] = new ExistingFile(new NonEmptyStringType($this->shopSource . DIRECTORY_SEPARATOR
                    . $destinationFile));
            } else {
                $overloadFilesList[] = new ExistingFile(new NonEmptyStringType($this->shopSource . $sourceFile));
            }
        }


        return CustomThemeCopyResponse::createWithOverloadsAndWhitelist(new ExistingFileCollection($overloadFilesList),
            new ExistingFileCollection($whitelist));
    }


    /**
     * @param ThemeId                     $themeId
     * @param                             $directory
     * @param ThemeDirectoryRootInterface $destination
     *
     * @throws FileNotFoundException
     */
    protected function _copyCustomJsDirectory(
        ThemeId $themeId,
        ThemeDirectoryInterface $directory,
        ThemeDirectoryRootInterface $destination): void
    {

        $childDirectories = $directory->getChildren();
        if ($childDirectories) {
            foreach ($childDirectories as $childDirectory) {
                $this->_copyCustomJsDirectory($themeId, $childDirectory, $destination);
            }
        }
        [$sourcePath, $destinationPath] = $this->getPaths($themeId, $directory, $destination);
        foreach ($directory->getFiles() as $file) {
            $sourceFile = $sourcePath . DIRECTORY_SEPARATOR . $file;
            $destinationFile = $this->_moveJsFromCustomRootDirectoryToCustomGlobalDirectory($destinationPath
                . DIRECTORY_SEPARATOR
                . $file,
                $sourceFile);
            if ($this->filesystem->has($destinationFile)) {
                $this->_appendFileContent($destinationFile, $sourceFile);
            } else {
                $this->_copyFile($sourceFile, $destinationFile);
            }
        }
    }


    /**
     * @param ThemeId                     $themeId
     * @param                             $directory
     * @param ThemeDirectoryRootInterface $destination
     *
     * @throws FileNotFoundException
     */
    protected function _copyCustomStylesDirectory(
        ThemeId $themeId,
        ThemeDirectoryInterface $directory,
        ThemeDirectoryRootInterface $destination
    ): void {
        $childDirectories = $directory->getChildren();
        if ($childDirectories) {
            foreach ($childDirectories as $childDirectory) {
                $this->_copyCustomStylesDirectory($themeId, $childDirectory, $destination);
            }
        }
        [$sourcePath, $destinationPath] = $this->getPaths($themeId, $directory, $destination);
        foreach ($directory->getFiles() as $file) {
            $sourceFile      = $sourcePath . DIRECTORY_SEPARATOR . $file;
            $destinationFile = $destinationPath . DIRECTORY_SEPARATOR . $file;

            if (!$this->filesystem->has($destinationFile)) {
                $destinationFile = $destinationPath . '/custom/_usermod.scss';
            }

            if($this->filesystem->has($destinationFile)){
                $this->_appendFileContent($destinationFile, $sourceFile);
            } else {
                $this->_copyFile($sourceFile, $destinationFile);
            }

        }

    }


    /**
     * @param ThemeId                      $themeId
     * @param                              $imagesDirectory
     * @param ThemeDirectoryRootInterface  $destination
     *
     * @throws FileNotFoundException
     */
    protected function _copyThemeImagesDirectory(
        ThemeId $themeId,
        ThemeDirectoryInterface $imagesDirectory,
        ThemeDirectoryRootInterface $destination
    ): void {
        $childDirectories = $imagesDirectory->getChildren();
        if ($childDirectories) {
            foreach ($childDirectories as $childDirectory) {
                $this->_copyThemeImagesDirectory($themeId, $childDirectory, $destination);
            }
        }

        foreach ($imagesDirectory->getFiles() as $file) {
            $sourcePath      = $this->_getRelative($imagesDirectory->getRoot());
            $destinationPath = $this->_getRelative($destination) . $this->_stringAfter($themeId->getId(), $sourcePath);

            $destinationPath = $this->_replaceCustomInPath($destinationPath);


            $sourceFile      = $sourcePath . DIRECTORY_SEPARATOR . $file;
            $destinationFile = $destinationPath . DIRECTORY_SEPARATOR . $file;
            $this->_copyOrReplaceFile($sourceFile, $destinationFile);
        }
    }


    /**
     * @param ThemeId                      $themeId
     * @param                              $directory
     * @param ThemeDirectoryRootInterface  $destination
     *
     * @throws FileNotFoundException
     */
    protected function _copyStyleEditThemeDirectory(
        ThemeId $themeId,
        ThemeDirectoryInterface $directory,
        ThemeDirectoryRootInterface $destination
    ): void {
        $childDirectories = $directory->getChildren();
        if ($childDirectories) {
            foreach ($childDirectories as $childDirectory) {
                $this->_copyThemeDirectory($themeId, $childDirectory, $destination);
            }
        }

        foreach ($directory->getFiles() as $file) {
            // ignores files that are created by styleedit
            if (!$themeId->equals($this->sourceThemeId)
                &&$this->_endsWith($file, '.json')
                && $this->_endsWith($directory->getRoot()->getPath(), 'styleedit')) {
                continue;
            }
            $this->_copyOrReplaceThemeFile($themeId, $directory, $destination, $file);

        }
    }

    /**
     * @param ExistingFile $file
     *
     * @return string
     */
    protected function _getRelativeFromFile(ExistingFile $file): string
    {
        return str_replace($this->shopSource, '', $file->getFilePath());
    }


    /**
     * @param $sourceFile
     * @param $destinationFile
     * @param $suffix
     *
     * @return mixed
     * @throws FileNotFoundException
     */
    protected function _extendHtmlFile($sourceFile, $destinationFile, $suffix) : ExistingFile
    {
        $fileExtension = '.' .pathinfo($destinationFile, PATHINFO_EXTENSION);
        $filename = pathinfo($destinationFile, PATHINFO_FILENAME);
        $path = pathinfo($destinationFile, PATHINFO_DIRNAME);

        $count = 0;
        do {
            $renamed = $path . DIRECTORY_SEPARATOR . $filename . '.' . $count . $fileExtension;
            $count++;
        } while ($this->filesystem->has($renamed));

        $newFileName = pathinfo($renamed, PATHINFO_BASENAME);


        try {
            if($this->filesystem->has($destinationFile)){
                $this->filesystem->rename($destinationFile, $renamed);
            }
            $this->filesystem->copy($sourceFile, $destinationFile);

        } catch (FileExistsException | FileNotFoundException $e) {
            //suppress the exception
        }


        $extends = "{*==========================================================================\n";
        $extends .= "  Source File: $sourceFile\n";
        $extends .= "  ========================================================================== *}\n";

        $extends .= sprintf('{extends file="get_usermod:{$tpl_path}%s"}', $newFileName);

        $resource = $this->filesystem->readStream($destinationFile);
        $content  = $extends . PHP_EOL . PHP_EOL . stream_get_contents($resource);

        $putStream = tmpfile();
        fwrite($putStream, $content);
        rewind($putStream);

        $this->filesystem->putStream($destinationFile, $putStream);

        if (is_resource($putStream)) {
            fclose($putStream);
        }

        return new ExistingFile(new NonEmptyStringType($this->shopSource . $renamed));
    }


    /**
     * @param $searchFor
     * @param $subject
     *
     * @return bool|string
     */
    protected function _stringAfter($searchFor, $subject)
    {
        return substr($subject, strpos($subject, $searchFor) + strlen($searchFor));
    }


    /**
     * @param $haystack
     * @param $needle
     *
     * @return bool
     */
    protected function _endsWith($haystack, $needle): bool
    {
        $length = strlen($needle);
        if ($length === 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }


    /**
     * @param string $path
     *
     * @return string
     */
    protected function _replaceCustomInPath($path): string
    {
        $pat = '#\/variants\/.*custom#';

        if (preg_match($pat, str_replace('\\', '/', $path)) === 0) {
            return $this->_stringReplaceFirst('custom', 'system', $path);
        }

        return $path;
    }


    /**
     * Modifies the path if a file would be placed inside the root of the
     * custom directory. The file will be moved inside the global directory of
     * system directory
     *
     * This method is only called for child themes
     *
     * @param string $path
     * @param string $source
     *
     * @return string mixed
     */
    protected function _moveJsFromCustomRootDirectoryToCustomGlobalDirectory($path, $source): string
    {
        $fileIsInCustomRootDirectoryPattern = '#custom/([^/]+\.js)$#';

        //  detecting if the source file is in the root of the custom directory
        if (preg_match($fileIsInCustomRootDirectoryPattern, $source) !== 0) {

            //  SHOP_ROOT ends with slash and paths starts with a slash
            $publicPath = SHOP_ROOT . substr($path, 1);

            //  Prohibiting changing of paths that already exist in the root of the system directory
            if (!file_exists($publicPath)) {

                $fileInSystemRootDirectoryPattern = '#system/([^/]+\.js)$#';

                //  the path for the directory has already been changed in a
                //  previous method without viewing the file name
                return preg_replace($fileInSystemRootDirectoryPattern, 'system/Global/$1', $path);
            }
        }

        return $path;
    }


    /**
     * @param $destinationFile
     * @param $sourceFile
     *
     */
    protected function _appendFileContent($destinationFile, $sourceFile): void
    {
        $putStream = null;
        try {
            $resource = $this->filesystem->readStream($destinationFile);

            $content   = stream_get_contents($resource) . PHP_EOL . $this->filesystem->read($sourceFile);
            $putStream = tmpfile();
            fwrite($putStream, $content);
            rewind($putStream);

            $this->filesystem->putStream($destinationFile, $putStream);


        } catch (FileNotFoundException $e) {
            //ignore the exception
        }
        finally {
            if (is_resource($putStream)) {
                fclose($putStream);
            }
        }

    }


    /**
     * @param $search
     * @param $replace
     * @param $subject
     *
     * @return string
     */
    protected function _stringReplaceFirst($search, $replace, $subject): string
    {
        $pos = strpos($subject, $search);
        if ($pos !== false) {
            return substr($subject, 0, $pos) . $replace . substr($subject, $pos + strlen($search));
        }

        return $subject;
    }


    /**
     * @param ThemeId                      $themeId
     * @param                              $directory
     * @param ThemeDirectoryRootInterface  $destination
     *
     * @throws FileNotFoundException
     */
    protected function _copyThemeFontsDirectory(
        ThemeId $themeId,
        ThemeDirectoryInterface $directory,
        ThemeDirectoryRootInterface $destination
    ): void {
        $childDirectories = $directory->getChildren();
        if ($childDirectories) {
            foreach ($childDirectories as $childDirectory) {
                $this->_copyThemeFontsDirectory($themeId, $childDirectory, $destination);
            }
        }

        $sourcePath      = $this->_getRelative($directory->getRoot());
        $destinationPath = $this->_getRelative($destination) . $this->_stringAfter($themeId->getId(), $sourcePath);
        foreach ($directory->getFiles() as $file) {
            $sourceFile      = $sourcePath . DIRECTORY_SEPARATOR . $file;
            $destinationFile = $destinationPath . DIRECTORY_SEPARATOR . $file;
            $this->_copyOrReplaceFile($sourceFile, $destinationFile);
        }

    }


    /**
     * @param string                      $gxModuleThemeId
     * @param ThemeDirectoryRootInterface $destination
     *
     * @param string                      $group
     *
     * @return bool|CustomThemeCopyResponse
     * @throws FileNotFoundException
     */
    protected function _copyGxModulesExtensionsForTheme(
        string $gxModuleThemeId,
        ThemeDirectoryRootInterface $destination,
        string $group = 'core'
    ) {

        $result          = [];

        $gxModuleThemeId = strtolower($gxModuleThemeId);

        if (isset($this->themeExtensionFiles[$gxModuleThemeId][$group]['html'])) {

            foreach ($this->themeExtensionFiles[$gxModuleThemeId][$group]['html'] as $file) {
                $relativeSourcePath      = str_replace($this->shopSource, '', $file);
                $relativeSourcePath      = str_replace(str_replace('\\', '/', $this->shopSource), '',
                    $relativeSourcePath);
                $relativeDestinationPath = $this->_getRelative($destination->withPath(static::HTML_SYSTEM_DIRECTORY))
                                           . DIRECTORY_SEPARATOR . basename($file);

                if ($this->filesystem->has($relativeDestinationPath)) {
                    $result[] = $this->_extendHtmlFile($relativeSourcePath,
                        $relativeDestinationPath,
                        'GXModules_' . $gxModuleThemeId);
                } else {
                    $this->_copyFile($relativeSourcePath, $relativeDestinationPath);
                }
            }
        }
        if (isset($this->themeExtensionFiles[$gxModuleThemeId][$group]['javascript']['system'])) {
            foreach ($this->themeExtensionFiles[$gxModuleThemeId][$group]['javascript']['system'] as $file) {
                $relativeSourcePath      =  $this->_getRelativeFromFile(new ExistingFile(new NonEmptyStringType($file)));
                $systemFolder = $destination->withPath(static::JS_SYSTEM_DIRECTORY);
                $relativeDestinationPath = $this->_getRelative($systemFolder)
                    . DIRECTORY_SEPARATOR
                    . $this->_getRelativeFromDir($file, '/javascript/system/');
                $this->_copyOrReplaceFile($relativeSourcePath, $relativeDestinationPath);
            }
        }

        return CustomThemeCopyResponse::createWithOverloadsAndWhitelist(new ExistingFileCollection([]),
            new ExistingFileCollection($result));
    }


    /**
     * @param MainThemeInterface          $mainTheme
     * @param array                       $childThemes
     * @param ThemeDirectoryRootInterface $destination
     *
     * @throws FileNotFoundException
     */
    protected function _applyGxModulesCustomOverloads(
        MainThemeInterface $mainTheme,
        array $childThemes,
        ThemeDirectoryRootInterface $destination
    ): void {
        $this->_copyGxModulesExtensionsForTheme('all', $destination, 'custom');
        $this->_copyGxModulesExtensionsForTheme($mainTheme->getId()->getId(), $destination, 'custom');
        foreach ($childThemes as $childTheme) {
            $this->_copyGxModulesExtensionsForTheme($childTheme->getId()->getId(), $destination, 'custom');
        }
    }

    /**
     * @param ThemeId                     $themeId
     * @param ThemeDirectoryInterface     $directory
     * @param ThemeDirectoryRootInterface $destination
     * @param string                      $file
     */
    protected function _copyOrReplaceThemeFile(ThemeId $themeId,
        ThemeDirectoryInterface $directory,
        ThemeDirectoryRootInterface $destination,
        string $file) : void
    {
        [$sourcePath, $destinationPath] = $this->getPaths($themeId, $directory, $destination);
        $sourceFile      = $sourcePath . DIRECTORY_SEPARATOR . $file;
        $destinationFile = $destinationPath . DIRECTORY_SEPARATOR . $file;
        $this->_copyOrReplaceFile($sourceFile, $destinationFile);


    }

    /**
     * @param string $sourceFile
     * @param $destinationFile
     */
    protected function _copyOrReplaceFile(string $sourceFile, $destinationFile): void
    {
        if ($this->filesystem->has($sourceFile)) {
            try {
                if ($this->filesystem->has($destinationFile)) {
                    $this->filesystem->delete($destinationFile);
                }
                $this->filesystem->copy($sourceFile, $destinationFile);
            } catch (FileNotFoundException | FileExistsException $e) {
                //suppress the exception
            }
        }
    }

    /**
     * @param ThemeId $themeId
     * @param ThemeDirectoryInterface $directory
     * @param ThemeDirectoryRootInterface $destination
     * @param string $file
     */
    protected function _copyMainThemeFile(ThemeId $themeId,
        ThemeDirectoryInterface $directory,
        ThemeDirectoryRootInterface $destination,
        string $file): void
    {
        $sourcePath      = $this->_getRelative($directory->getRoot());
        $destinationPath = $this->_getRelative($destination) . $this->_stringAfter($themeId->getId(), $sourcePath);

        $sourceFile      = $sourcePath . DIRECTORY_SEPARATOR . $file;
        $destinationFile = $destinationPath . DIRECTORY_SEPARATOR . $file;
        $this->_copyFile($sourceFile, $destinationFile);

    }

    /**
     * @param string $sourceFile
     * @param string $destinationFile
     */
    protected function _copyFile(string $sourceFile, string $destinationFile): void
    {
        try {
            $this->filesystem->copy($sourceFile, $destinationFile);
        } catch (FileExistsException | FileNotFoundException $e) {
            //suppress the exception
        }
    }

    /**
     * @param ThemeId $themeId
     * @param ThemeDirectoryInterface $directory
     * @param ThemeDirectoryRootInterface $destination
     * @return array
     */
    public function getPaths(
        ThemeId $themeId,
        ThemeDirectoryInterface $directory,
        ThemeDirectoryRootInterface $destination): array
    {
        $sourcePath      = $this->_getRelative($directory->getRoot());
        $destinationPath = $this->_getRelative($destination) . $this->_stringAfter($themeId->getId(), $sourcePath);
        $destinationPath = $this->_replaceCustomInPath($destinationPath);
        return [$sourcePath, $destinationPath];
    }


    /**
     * @param        $file
     * @param string $dir
     *
     * @return string
     */
    protected function _getRelativeFromDir($file, string $dir): string
    {
        $pos = stripos($file, $dir);
        if($pos !== false){
            $pos += strlen($dir);
            $pos -= strlen($file);
            return substr($file, $pos);
        }
        return '';

    }
    
    
    /**
     * @param string $path
     * @param int    $mode
     */
    protected function recursiveChmod(string $path, int $mode = 0777): void
    {
        if (is_dir($path)) {
            
            $dir = new DirectoryIterator($path);
        
            foreach ($dir as $item) {
                
                $path = $item->getPathname();
    
                if ($item->isDot()) {
                
                    continue;
                }
                
                $item->isDir() ? $this->recursiveChmod($path, $mode) : chmod($path, $mode);
            }
            
            unset($dir);
        }
        
        chmod($path, $mode);
    }
}
