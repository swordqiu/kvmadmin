drop table if exists user_tbl;

create table user_tbl (
	id		int unsigned auto_increment,
	vc_user		varchar(32),
	vc_password	varchar(64),
	vc_roles	varchar(255),
	iu_group	int unsigned,
	vc_groups	varchar(255),
	vc_name		varchar(128),
	primary key (id),
	index (vc_user)
);

drop table if exists role_tbl;

create table role_tbl (
	vc_name	varchar(64),
	vc_desc blob,
	primary key (vc_name)
);

drop table if exists group_tbl;

create table group_tbl (
	id	int unsigned auto_increment,
	vc_name varchar(64),
	vc_parent varchar(64),
	vc_desc blob,
	primary key (id),
	index (vc_name),
	index (vc_name, vc_parent)
);

drop table if exists vm_host_tbl;

create table vm_host_tbl (
	id		int unsigned auto_increment,
	vc_name		varchar(64) CHARACTER SET ascii,
	vc_address	varchar(64) CHARACTER SET ascii,
	siu_memory	smallint unsigned,
	siu_disk_size	smallint unsigned,
	vc_bridge_name  varchar(16) CHARACTER SET ascii,
	vc_vm_dir	varchar(255) CHARACTER SET ascii,
	vc_template_dir	varchar(255) CHARACTER SET ascii,
	vc_iso_dir	varchar(255) CHARACTER SET ascii,
	dt_created	datetime,
	primary key (id)
);

drop table if exists vm_host_net_tbl;

create table vm_host_net_tbl (
	id		int unsigned auto_increment,
	host_id		int unsigned,
	net_id		int unsigned,
	vc_bridge	varchar(16) CHARACTER SET ascii,
	vc_eth		varchar(16) CHARACTER SET ascii,
	dt_created	datetime,
	primary key (id)
);

drop table if exists vm_network_tbl;

create table vm_network_tbl (
	id	      int unsigned auto_increment,
	vc_name       varchar(64) CHARACTER SET ascii,
	vc_gateway    varchar(64) CHARACTER SET ascii,
	vc_public_ip  varchar(64) CHARACTER SET ascii,
	tiu_netmask   tinyint unsigned,
	siu_vlan_id   smallint unsigned,
	vc_addr_start varchar(64) CHARACTER SET ascii,
	vc_addr_end   varchar(64) CHARACTER SET ascii,
	dt_created    datetime,
	primary key (id)
);

drop table if exists vm_template_tbl;

create table vm_template_tbl (
	id         int unsigned auto_increment,
	vc_name    varchar(64) CHARACTER SET ascii,
	vc_os      varchar(32) CHARACTER SET ascii,
	vc_version varchar(32) CHARACTER SET ascii,
	tiu_bits   tinyint unsigned,
	siu_size   smallint unsigned,
	vc_if	   varchar(16) CHARACTER SET ascii,
	vc_cache   varchar(32) CHARACTER SET ascii,
	vc_aio     varchar(16) CHARACTER SET ascii,
	vc_lang    varchar(16) CHARACTER SET ascii,
	dt_created datetime,
	primary key (id)
);

drop table if exists vm_guest_tbl;

create table vm_guest_tbl (
	id		int unsigned auto_increment,
	host_id		int unsigned not null,
	vc_name		varchar(64) CHARACTER SET ascii,
	siu_memory	smallint unsigned,
	siu_vnc_port	smallint unsigned,
	tiu_state	tinyint unsigned,
	vc_iso		varchar(255) CHARACTER SET ascii,
	vc_user		varchar(32) CHARACTER SET ascii,
	vc_bootorder	varchar(4) CHARACTER SET ascii,
	dt_created	datetime,
	primary key (id)
);

drop table if exists vm_guest_net_tbl;

create table vm_guest_net_tbl (
	id              int unsigned auto_increment,
	guest_id	int unsigned,
	net_id		int unsigned,
	vc_mac		varchar(32) CHARACTER SET ascii,
	vc_addr         varchar(64) CHARACTER SET ascii,
	vc_model	varchar(16) CHARACTER SET ascii,
	iu_sndbuf	int unsigned,
	dt_created	datetime,
	primary key (id)
);

# alter table vm_guest_net_tbl add column vc_model varchar(16) CHARACTER SET ascii;
# alter table vm_guest_net_tbl add column iu_sndbuf int unsigned default 0;
# update vm_guest_net_tbl set vc_model='virtio';

drop table if exists vm_guest_net_portmap_tbl;

create table vm_guest_net_portmap_tbl (
	id		int unsigned auto_increment,
	guest_net_id	int unsigned,
	vc_protocol     varchar(16) CHARACTER SET ascii,
	siu_pub_port	int unsigned,
	siu_pri_port	int unsigned,
	tiu_state	tinyint unsigned default 0,
	dt_created	datetime,
	primary key (id)
);

# alter table vm_guest_net_portmap_tbl add column tiu_state tinyint unsigned default 0;

drop table if exists vm_guest_disk_tbl;

create table vm_guest_disk_tbl (
	id		int unsigned auto_increment,
	guest_id	int unsigned,
	siu_disk_sz	smallint unsigned,
	vc_if		varchar(16) CHARACTER SET ascii,
	vc_cache	varchar(32) CHARACTER SET ascii,
	vc_aio		varchar(16) CHARACTER SET ascii,
	dt_created	datetime,
	primary key (id)
);

# alter table vm_guest_disk_tbl add column vc_if varchar(16) CHARACTER SET ascii;
# alter table vm_guest_disk_tbl add column vc_cache varchar(32) CHARACTER SET ascii;
# alter table vm_guest_disk_tbl add column vc_aio varchar(16) CHARACTER SET ascii;
# update vm_guest_disk_tbl set vc_if='virtio';
# update vm_guest_disk_tbl set vc_cache='writethrough';
# update vm_guest_disk_tbl set vc_aio='native';

drop table if exists vm_log_tbl;

create table vm_log_tbl (
	id bigint unsigned auto_increment,
	host_id int unsigned,
	vc_name varchar(64),
	vc_user varchar(32),
	dt_when datetime,
	notes   varchar(255),
	primary key (id)
);

drop view if exists vm_host_used_res_view;
create view vm_host_used_res_view as
select host_id, sum(siu_memory) as used_mem, count(*) as live_vm_count from vm_guest_tbl where tiu_state=1 group by host_id;

drop view if exists vm_guest_storage_view;
create view vm_guest_storage_view as 
select vm_guest_tbl.id, sum(siu_disk_sz) as guest_disk_size from vm_guest_tbl left join vm_guest_disk_tbl on vm_guest_tbl.id=vm_guest_disk_tbl.guest_id group by vm_guest_tbl.id;

drop view if exists vm_host_alloc_view;
create view vm_host_alloc_view as
select host_id, count(*) as vm_count, sum(guest_disk_size) as vm_disk_sz from vm_guest_tbl, vm_guest_storage_view where vm_guest_tbl.id=vm_guest_storage_view.id group by host_id;

drop view if exists vm_guest_nic_view;
create view vm_guest_nic_view as 
select guest_id, GROUP_CONCAT(vc_addr SEPARATOR ',') as vc_addr, GROUP_CONCAT(vc_mac SEPARATOR ',') as vc_mac from vm_guest_net_tbl group by guest_id;

drop view if exists vm_guest_disk_view;
create view vm_guest_disk_view as 
select guest_id, SUM(siu_disk_sz) as siu_disk_sz from vm_guest_disk_tbl group by guest_id;

