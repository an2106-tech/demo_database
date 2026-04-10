<?php
$c = file_get_contents('resources/views/livewire/client/home.blade.php');
preg_match_all('/@if/', $c, $m1);
preg_match_all('/@endif/', $c, $m2);
echo "if: " . count($m1[0]) . " endif: " . count($m2[0]) . "\n";

preg_match_all('/@foreach/', $c, $m3);
preg_match_all('/@endforeach/', $c, $m4);
echo "foreach: " . count($m3[0]) . " endforeach: " . count($m4[0]) . "\n";

preg_match_all('/@forelse/', $c, $m5);
preg_match_all('/@endforelse/', $c, $m6);
echo "forelse: " . count($m5[0]) . " endforelse: " . count($m6[0]) . "\n";

preg_match_all('/@auth/', $c, $m7);
preg_match_all('/@endauth/', $c, $m8);
echo "auth: " . count($m7[0]) . " endauth: " . count($m8[0]) . "\n";

preg_match_all('/@php/', $c, $m9);
preg_match_all('/@endphp/', $c, $m10);
echo "php: " . count($m9[0]) . " endphp: " . count($m10[0]) . "\n";

preg_match_all('/<\?php/', $c, $m11);
preg_match_all('/\?>/', $c, $m12);
echo "<?php: " . count($m11[0]) . " ?>: " . count($m12[0]) . "\n";
