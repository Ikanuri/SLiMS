<?php

/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

/* Stock Take module submenu items */

// IP based access limitation
do_checkIP('smc');
do_checkIP('smc-stocktake');

$menu['stock_take.header-stock-take'] = array('Header', __('STOCK TAKE'));
$menu['stock_take.stock-take-history'] = array(__('Stock Take History'), MWB.'stock_take/index.php', __('View Stock Take History'));

if(isset($for_select_privileges) && $for_select_privileges) {
    $menu['stock_take.initialize'] = array(__('Initialize'), MWB.'stock_take/init.php', __('Initialize New Stock Take Proccess'));
}

// check if there is any active stock take proccess
$stk_query = $dbs->query('SELECT * FROM stock_take WHERE is_active=1');
if ($stk_query->num_rows || (isset($for_select_privileges) && $for_select_privileges)) {
    $menu['stock_take.current-stock-take'] = array(__('Current Stock Take'), MWB.'stock_take/current.php', __('View Current Stock Take Process'));
    $menu['stock_take.stock-take-report'] = array(__('Stock Take Report'), MWB.'stock_take/st_report.php', __('View Current Stock Take Report'));
    $menu['stock_take.current-lost-item'] = array(__('Current Lost Item'), MWB.'stock_take/lost_item_list.php', __('View Lost Item in Current Stock Take Proccess'));
    $menu['stock_take.stock-take-log'] = array(__('Stock Take Log'), MWB.'stock_take/st_log.php', __('View Log of Current Stock Take Proccess'));
    $menu['stock_take.upload-list'] = array(__('Upload List'), MWB.'stock_take/st_upload.php', __('Upload List in text file'));
    $menu['stock_take.resynchronize'] = array(__('Resynchronize'), MWB.'stock_take/resync.php', __('Resynchronize bibliographic data with current stock take'));
    $menu['stock_take.finish-stock-take'] = array(__('Finish Stock Take'), MWB.'stock_take/finish.php', __('Finish Current Stock Take Proccess'));
} else {
    $menu['stock_take.initialize'] = array(__('Initialize'), MWB.'stock_take/init.php', __('Initialize New Stock Take Proccess'));
}


