@echo off
setlocal enabledelayedexpansion

:: Create the output directory if it doesn't exist
if not exist "output" mkdir output

:: Create a log file
set log=process_log.txt
echo Logging started at %date% %time% > %log%

:: Process each image
for %%f in (*.png *.jpg) do (
    echo Processing %%f >> %log%
    set "filename=%%~nf"
    if /I "%%~xf"==".jpg" (
        echo Converting %%f to PNG >> %log%
        magick "%%f" "temp.png" 2>> %log%
        if errorlevel 1 (
            echo Failed to convert %%f to PNG >> %log%
            goto end
        )
        echo Making background transparent for temp.png >> %log%
        magick "temp.png" -fuzz 20%% -transparent white "output\!filename!.png" 2>> %log%
        if errorlevel 1 (
            echo Failed to make background transparent for temp.png >> %log%
            goto end
        )
        del "temp.png"
    ) else if /I "%%~xf"==".png" (
        echo Making background transparent for %%f >> %log%
        magick "%%f" -fuzz 20%% -transparent white "output\!filename!.png" 2>> %log%
        if errorlevel 1 (
            echo Failed to make background transparent for %%f >> %log%
            goto end
        )
    )
)
echo Logging completed at %date% %time% >> %log%
pause

:end
