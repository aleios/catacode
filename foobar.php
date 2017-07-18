<?php

// Generate numbers 1 to 100
for($num = 1; $num <= 100; $num++)
{
	if($num != 1)
		echo ", ";
	
	// First test if number is both divisible by 3 and 5
	if($num % 3 == 0 && $num % 5 == 0)
	{
		echo "foorbar";
	}
	else if($num % 3 == 0)
	{
		echo "foo";
	}
	else if($num % 5 == 0)
	{
		echo "bar";
	}
	else
	{
		echo $num;
	}
}

?>