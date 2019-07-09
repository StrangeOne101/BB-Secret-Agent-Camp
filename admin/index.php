<?php
if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "on" && $_SERVER["HTTP_HOST"] != "localhost")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

$open = true;
global $open;

include_once($_SERVER['DOCUMENT_ROOT'] . "/scripts/debug.php");

global $debugVal;
$debugVal = false;

include_once($_SERVER['DOCUMENT_ROOT'] . "/scripts/database.php");


if (isReady()) {
    session_start([
        'cookie_lifetime' => 60,
    ]);
    
    if (isset($_POST['submit'])) { //If they have just tried to login
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        if (isValidLogin($email, $password)) {
            $_SERVER['email'] = $email;
            $_SESSION['username'] = $email; //Deprecated
            $_SESSION['email'] = $email;
            //echo "Successfully logged in";
        } else {
            //echo "Invalid login";
        }
    }
    
    if(isset($_SESSION['email'])){ //If they managed to log in.
        $loggedIn = true;
        $email = $_SESSION['email'];
        //echo "Welcome, " . $_SESSION['firstname'];
    } else {
        session_destroy();
        header("Location: https://" . $_SERVER["HTTP_HOST"] . "/admin/login.php");
    }
} else {
    //This reads the error_database_admin.html file and echos the content here. It's easier than
    //writing the entire HTML document in this script file, as HTML documents can be
    //rather large.
    $myfile = fopen($_SERVER['DOCUMENT_ROOT'] . "/admin/pages/error_database_admin.html", "r"); //Open the file
    if ($myfile == null) {
        echo "Something went really wrong!"; //o shit son
        return "";
    }
    echo str_replace('$errors', getErrors(), fread($myfile,filesize($_SERVER['DOCUMENT_ROOT'] . "/admin/pages/error_database_admin.html"))); //Echo the data, and fill in the errors
    fclose($myfile); //Because we are a tidy kiwi
}

class AdminPage {
    public $pageData;
    public $pageName;
    public $jsFile;
    public $cssFile;
    public $glyph;

    function __construct($pageName, $pageData, $glyph, $cssFile, $jsFile) {
        $this->pageName = $pageName;
        $this->pageData = $pageData;
        $this->glyph = $glyph;
        $this->cssFile = $cssFile;
        $this->jsFile = $jsFile;
    }

    /**
     * Generates the <script> tags for this page
     * @return string
     */
    function generateJS() {
        if ($this->jsFile != null) {
            return "<script src=\"./js/" . $this->jsFile . ".js\"></script>";
        }
        return "";
    }

    /**
     * Generates the <link> tags for this page
     * @return string
     */
    function generateCSS() {
        if ($this->cssFile != null) {
            return "<link rel=\"stylesheet\" href=\"./css/" . $this->cssFile . ".css\">";
        }
        return "";
    }

    /**
     * Gets the HTML to insert into the navbar on the left
     * @return string The HTML to insert
     */
    public function getNavData() {
        return "<li class=\"nav-item\" >\n<a class=\"nav-link nav-tab-that-i-must-find\" href=\"#tab_" . $this->pageName . "\" data-toggle=\"tab\">\n<span data-feather=\"" . $this->glyph . "\"></span>\n" . $this->pageName . "<span class=\"sr-only\">(current)</span>\n</a>\n </li>\n";
    }

    /**
     * Gets the content for the page
     * @return string The HTML content
     */
    public function getContent() {
        return "<div id=\"tab_" . $this->pageName . "\" class=\"tab-pane page-content-that-i-must-find\">\n" . $this->pageData . "</div>\n";
    }
}

$pages = array();

/**
 * @param string $pageName The name of the page to load. Should be in english.
 * @param string $pageFilename The filename of the page to be loaded.
 * @param string $glyph The bootstrap glyph name for the tab
 * @param string $cssFileName The name of the stylesheet to use for this page. Use null for none
 * @param string $jsFileName The name of the JS file to use for the page. Use null for none
 * @return void
 */
function loadPage($pageName, $pageFilename, $glyph, $cssFileName, $jsFileName) {
    global $pages; //Get the array

    //Open the located file
    $myfile = fopen($_SERVER['DOCUMENT_ROOT'] . "/admin/pages/" . $pageFilename, "r"); //Open the file
    if ($myfile == null) {
        debug("A page has been misplaced! " . $pageFilename . " couldn't be found!");
        return "";
    }
    $page = new AdminPage($pageName, fread($myfile,filesize($_SERVER['DOCUMENT_ROOT'] . "/admin/pages/" . $pageFilename)), $glyph, $cssFileName, $jsFileName);
    array_push($pages, $page);
}

function addDivider($dividerName) {
    global $pages; //Get the array
    $html = "<h6 class=\"sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted\"><span>$dividerName</span></h6>";
    array_push($pages, $html);
}

loadPage("Dashboard", "admin_dashboard.html", "home", null, "admin_dashboard");
loadPage("Registration Data", "admin_data.html", "users", null, "admin_data");
loadPage("Fee Management", "admin_fees.html", "credit-card", null, null);
loadPage("Send Camp Email", "admin_email.html", "mail", null, "admin_email");
loadPage("Invite Viewers", "admin_dataviewing.html", "send", null, "admin_dataviewers");
loadPage("Statistics", "admin_statistics.html", "pie-chart", null, "admin_statistics");
addDivider("Panel Options");
loadPage("Admin Registration", "admin_signup.html", "clipboard", "admin_signup", "admin_signup");
loadPage("Panel Changelog", "changelog.html", "clock", "changelog", null);

$jsData = "";
$cssData = "";
$tabData = "";
$contentData = "";
foreach ($pages as &$key) {
    if (is_string($key)) {
        $tabData = $tabData . $key . "\n";
        continue;
    }
    $jsData = $jsData . "\n" . $key->generateJS();
    $cssData = $cssData . "\n" . $key->generateCSS();
    $tabData = $tabData . $key->getNavData();
    $contentData = $contentData . $key->getContent();
}

//Open the index page. This is where all the HTML for the base page lies.
$indexPage = fopen($_SERVER['DOCUMENT_ROOT'] . "/admin/pages/admin_index.html", "r"); //Open the file
if ($indexPage == null) {
    echo "Something went really wrong!"; //o shit son
    return "";
}
$pageData = fread($indexPage,filesize($_SERVER['DOCUMENT_ROOT'] . "/admin/pages/admin_index.html")); //Read the actual data
$pageData = str_replace("<customjs/>", $jsData, $pageData);   //Replace the JS and CSS custom tags we
$pageData = str_replace("<customcss/>", $cssData, $pageData); //made and place our js/css in there
$pageData = str_replace("<customtabs/>", $tabData, $pageData); //Put the tab on the left
$pageData = str_replace("<customtabscontent/>", $contentData, $pageData); //Put the page on

$variables = array("email" => $_SESSION['email'], "firstname" =>$_SESSION['firstname']);
foreach($variables as $key => $value) {
    $pageData = str_replace('$' . $key, htmlspecialchars($value), $pageData);
}
echo $pageData;

?>