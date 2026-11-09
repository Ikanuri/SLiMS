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

// be sure that this file not accessed directly
if (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

/* Custom reports list */

$menu['reporting.custom-recap'] = array(__('Custom Recapitulations'), MWB.'reporting/customs/class_recap.php', __('Title and Collection recapitulation based on classification and others'));
$menu['reporting.title-list'] = array(__('Title List'), MWB.'reporting/customs/titles_list.php', __('List of bibliographic titles'));
$menu['reporting.item-title-list'] = array(__('Items Title List'), MWB.'reporting/customs/item_titles_list.php', __('List of collection/items'));
$menu['reporting.item-usage-statistics'] = array(__('Items Usage Statistics'), MWB.'reporting/customs/item_usage.php', __('List of Collection/items usage statistic'));
$menu['reporting.loans-by-classification'] = array(__('Loans by Classification'), MWB.'reporting/customs/loan_by_class.php', __('Loan statistic by classification'));
$menu['reporting.member-list'] = array(__('Member List'), MWB.'reporting/customs/member_list.php', __('List of library member/patron'));
$menu['reporting.loan-list-by-member'] = array(__('Loan List by Member'), MWB.'reporting/customs/member_loan_list.php', __('List of loan by each member'));
$menu['reporting.loan-history'] = array(__('Loan History'), MWB.'reporting/customs/loan_history.php', __('Loan History Overview'));
$menu['reporting.due-date-warning'] = array(__('Due Date Warning'), MWB.'reporting/customs/due_date_warning.php', __('Loan Due Date Warnings'));
$menu['reporting.overdued-list'] = array(__('Overdued List'), MWB.'reporting/customs/overdued_list.php', __('View Members Having Overdues'));
$menu['reporting.staff-activity'] = array(__('Staff Activity'), MWB.'reporting/customs/staff_act.php', __('Staff activity log recapitulation'));
$menu['reporting.visitor-statistic'] = array(__('Visitor Statistic'), MWB.'reporting/customs/visitor_report.php', __('Visitor Statistic'));
$menu['reporting.visitor-statistic-by-day'] = array(__('Visitor Statistic (by Day)'), MWB.'reporting/customs/visitor_report_day.php', __('Visitor Statistic (by Day)'));
$menu['reporting.visitor-list'] = array(__('Visitor List'), MWB.'reporting/customs/visitor_list.php', __('Visitor List'));
$menu['reporting.fines-report'] = array(__('Fines Report'), MWB.'reporting/customs/fines_report.php', __('Fines Report'));
$menu['reporting.member-fines-list'] = array(__('Member Fines List'), MWB.'reporting/customs/member_fines_list.php', __('Member Fines List'));
$menu['reporting.procurement-report'] = array(__('Procurement Report'), MWB.'reporting/customs/procurement_report.php', __('Procurement Report'));
$menu['reporting.download-counter'] = array(__('Download Counter'), MWB.'reporting/customs/dl_counter.php', __('Downloaded Attachment Report'));