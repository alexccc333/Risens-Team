<?php
include 'Enum.php';

class UserEnum extends Enum {
	const ROLE_ANON = 0;
	const ROLE_MANAGER = 1;
	const ROLE_ADMIN = 2;
	const ROLE_MEGA_ADMIN = 3;	
	
	const COL_ID = 'id';
	const COL_NAME = 'name';
	const COL_PASSWORD = 'password';
	const COL_ROLE = 'role';
	const COL_AVAIL_ANIME = 'available_anime';
	const COL_AVAIL_MANGA = 'available_manga';
	const COL_COOKIE = 'cookie';
    const COL_ACTIVE = 'active';
}
