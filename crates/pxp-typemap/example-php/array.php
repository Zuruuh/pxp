<?php

dumpType([]);
dumpType([1, 2, 3]);
dumpType([1, true, "hello"]);

$items = [];

dumpType($items);

$items[] = 1;

dumpType($items);
dumpType($items[1]);