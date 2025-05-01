<?php

return [
    /*
     * The disk on which to store added files and derived images by default.
     * Choose one of the disks you've configured in config/filesystems.php.
     */
    'disk_name' => env('MEDIA_DISK', 's3'),

    /*
     * The maximum file size of an item in bytes. Adding a file
     * larger than this will result in an exception.
     */
// ... existing code ...
]; 