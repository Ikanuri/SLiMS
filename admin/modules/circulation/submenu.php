<?php
/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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

/* Circulation module submenu items */
// IP based access limitation
do_checkIP('smc');
do_checkIP('smc-circulation');

$menu['circulation.header-circulation'] = array('Header', __('CIRCULATION'));
$menu['circulation.start-transaction'] = array(__('Start Transaction'), MWB.'circulation/index.php?action=start', __('Start Circulation Transaction Proccess'));
$menu['circulation.quick-return'] = array(__('Quick Return'), MWB.'circulation/quick_return.php', __('Quick Return Collection'));
$menu['circulation.loan-rules'] = array(__('Loan Rules'), MWB.'circulation/loan_rules.php', __('View and Modify Circulation Loan Rules'));
$menu['circulation.loan-history'] = array(__('Loan History'), MWB.'reporting/customs/loan_history.php', __('Loan History Overview'));
$menu['circulation.due-date-warning'] = array(__('Due Date Warning'), MWB.'reporting/customs/due_date_warning.php', __('View Members That About to Have Overdues'));
$menu['circulation.overdued-list'] = array(__('Overdued List'), MWB.'reporting/customs/overdued_list.php', __('View Members Having Overdues'));
$menu['circulation.reserve-list'] = array(__('Reservation'), MWB.'reporting/customs/reserve_list.php', __('Reservation'));
$menu['circulation.header-maintenance'] = array('Header', __('MAINTENANCE'));
$menu['circulation.loan-history-maintenance'] = array(__('Loan History Maintenance'), MWB.'circulation/loan_history_maintenance.php', __('Loan History Maintenance'));