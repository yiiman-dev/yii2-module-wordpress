# PO/MO Editor
PO/MO Editor adds a versatile interface to WordPress for editing and recompiling translation files.

## .PO File Searching

Any .po files found within the wp-content directory will be listed for editing, with the associated project name and language identified for easier organization and filtering to find what file you need to edit.

Should you want to limit what directories are scanned, you can define the `POMOEDITOR_SCAN_BLACKLIST` and `POMOEDITOR_SCAN_WHITELIST` constants with a list of directories to exclude or exclusively include (preferably in your wp-config.php file). Multiple paths can be separated by a colon (:), similar to `$PATH` in Linux.

## Basic and Advanced File Editing

The editor by default only allows you to edit the translated text value of each translation entry. If you need to edit the source text or context values, you can click *Enable Advanced Editing*, which will also enable editing of the files headers and other metadata.

Each entry must be explicitly saved or the changes to be recorded. When you're done making changes to the file, click *Save Translations* to have the .po updated an the .mo file recompiled from it.

As a precaution, the system will backup the original files before overwriting them with the updated data. You will however need to manually restore them via FTP or some other file management method.