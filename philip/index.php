<html>
<head>
    <title>Test Front Page</title>
    <link rel="stylesheet" type="text/css" media="screen" href="style.css" />
    <script type="text/javascript" src="style.js"></script>
</head>
<body>
<?php
$classes = array();
$classes[] = "brown";
$classes[] = "black";
$classes[] = "green";
$i=rand(0,2);

echo "<table>\n";
for ($y=0;$y<5;$y++) {
    echo "<tr>";
    $i=$y;
    if ($i>2) {
        $i-=3;
    }
    for ($x=0;$x<15;$x++) {
        echo "<td id=\"" . $x . $y . "\" class=\"" . $classes[$i] . "\" onmouseover=\"changeColor('$x$y');\"></td>";
        if ($i == 2) {
            $i = 0;
        }
        else {
            $i++;
        }
        #$i=rand(0,2);
    }
    echo "</tr>\n";
}
?>
</body>
</html>