<?php
$img = imagecreatefrompng(__DIR__ . '/donma logo.png');
$w = imagesx($img); $h = imagesy($img);
echo "Size: {$w}x{$h}\n";
$colors = [];
for($x=0;$x<$w;$x+=3){
  for($y=0;$y<$h;$y+=3){
    $rgb = imagecolorat($img,$x,$y);
    $c = imagecolorsforindex($img,$rgb);
    if($c['alpha'] < 80){
      $r = round($c['red']/15)*15;
      $g = round($c['green']/15)*15;
      $b = round($c['blue']/15)*15;
      $key = sprintf('#%02x%02x%02x',$r,$g,$b);
      $colors[$key] = ($colors[$key] ?? 0) + 1;
    }
  }
}
arsort($colors);
$i=0;
foreach($colors as $k=>$v){ echo "$k = $v\n"; if(++$i>=15) break; }
