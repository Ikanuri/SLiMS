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

/* Master File module submenu items */
// IP based access limitation
do_checkIP('smc');
do_checkIP('smc-masterfile');

$menu['master_file.header-authority-files'] = array('Header', __('AUTHORITY FILES'));
$menu['master_file.gmd'] = array(__('GMD'), MWB.'master_file/index.php', __('General Material Designation'));
$menu['master_file.content-type'] = array(__('Content Type'), MWB.'master_file/rda_cmc.php?type=content', __('RDA Content Type'));
$menu['master_file.media-type'] = array(__('Media Type'), MWB.'master_file/rda_cmc.php?type=media', __('RDA Media Type'));
$menu['master_file.carrier-type'] = array(__('Carrier Type'), MWB.'master_file/rda_cmc.php?type=carrier', __('RDA Carrier Type'));
$menu['master_file.publisher'] = array(__('Publisher'), MWB.'master_file/publisher.php', __('Document Publisher'));
$menu['master_file.supplier'] = array(__('Supplier'), MWB.'master_file/supplier.php', __('Item Supplier'));
$menu['master_file.author'] = array(__('Author'), MWB.'master_file/author.php', __('Document Authors'));
$menu['master_file.subject'] = array(__('Subject'), MWB.'master_file/topic.php', __('Subject'));
$menu['master_file.location'] = array(__('Location'), MWB.'master_file/location.php', __('Item Location'));
$menu['master_file.header-lookup-files'] = array('Header', __('LOOKUP FILES'));
$menu['master_file.place'] = array(__('Place'), MWB.'master_file/place.php', __('Place Name'));
$menu['master_file.item-status'] = array(__('Item Status'), MWB.'master_file/item_status.php', __('Item Status'));
$menu['master_file.collection-type'] = array(__('Collection Type'), MWB.'master_file/coll_type.php', __('Collection Type'));
$menu['master_file.doc-language'] = array(__('Doc. Language'), MWB.'master_file/doc_language.php', __('Document Content Language'));
$menu['master_file.label'] = array(__('Label'), MWB.'master_file/label.php', __('Special Labels for Titles to Show Up On Homepage'));
$menu['master_file.frequency'] = array(__('Frequency'), MWB.'master_file/frequency.php', __('Frequency'));
$menu['master_file.header-tools'] = array('Header', __('TOOLS'));
$menu['master_file.visitor-room'] = array(__('Visitor Room'), MWB.'master_file/visitor_room.php', __('List of members comment about biblio'));
$menu['master_file.comment-management'] = array(__('Comment Management'), MWB.'master_file/detail_comment.php', __('List of members comment about biblio'));
$menu['master_file.cataloging-servers'] = array(__('Cataloging Servers'), MWB.'master_file/p2pservers.php', __('List of available Copy Cataloging Servers'));
$menu['master_file.item-code-pattern'] = array(__('Item Code Pattern'), MWB.'master_file/item_code_pattern.php', __('Manage item code pattern'));
$menu['master_file.orphaned-author'] = array(__('Orphaned Author'), MWB.'master_file/author.php?type=orphaned', __('Orphaned Authors'));
$menu['master_file.orphaned-subject'] = array(__('Orphaned Subject'), MWB.'master_file/topic.php?type=orphaned', __('Orphaned Subject'));
$menu['master_file.orphaned-publisher'] = array(__('Orphaned Publisher'), MWB.'master_file/publisher.php?type=orphaned', __('Orphaned Publisher'));
$menu['master_file.orphaned-place'] = array(__('Orphaned Place'), MWB.'master_file/place.php?type=orphaned', __('Orphaned Place'));
