v1.0
# Jafu
*"Just Another File Uploader"*

Standalone file uploader and file validator class. Guarantees unique name. Can handle multiple optional file-upload inputs. Validates MIME-type against configurable allowed types.

* configuration: 
        
        1. rename config.php.dist to config.php
        2. Change the value of 'defaultSaveLocation' to the default save location on your system.
        
        Requires: >=php v5.6.0
        Composer: composer require erik404\jafu

See example.php for oversimplified example code explaining the public functions.


