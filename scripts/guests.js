function onGetVmStateSucc(c, r, params) {
	var state_str = ["停止", "运行"];
	var state_color = ['gray', 'green'];
	c.innerHTML = state_str[r._state];
	c.bgColor = state_color[r._state];
}

function showGuestList(records, panel, conf) {
	if(records.length == 0) {
		panel.innerHTML = '还没有配置虚拟机。';
	}else {
		var headers = [
			{
				title: '#',
				value: function(c, r) {
					c.innerHTML = r.id;
				}
			},
			{
				title: "主机名",
				value: function(c, r) {
					c.innerHTML = r.host_name;
				}
			},
			{
				title: "虚拟机名",
				value: function(c, r) {
					c.innerHTML = r.vc_name;
				}
			},
			{
				title: "内存(MB)",
				value: function(c, r) {
					c.innerHTML = r.siu_memory;
				}
			},
			{
				title: "硬盘(GB)",
				value: function(c, r) {
					c.innerHTML = r.siu_disk_sz;
				}
			},
			{
				title: "光盘",
				value: function(c, r) {
					if (r.vc_iso.length == 0) {
						c.innerHTML = 'None';
					}else {
						c.innerHTML = r.vc_iso;
					}
				}
			},
			{
				title: "网络",
				value: function(c, r) {
					c.innerHTML = r.vc_addr;
				}
			},
			{
				title: "VNC端口",
				value: function(c, r) {
					if(r.tiu_state == 1) {
						c.innerHTML = 5900 + Number(r.siu_vnc_port);
					}
				}
			},
			{
				title: "状态",
				value: function(c, r) {
					updateFieldContent(c, 'vm_state_' + r.host_id + '_' + r.id, 'GET_VM_STATE', {_id: r.id}, onGetVmStateSucc);
				}
			},
			{
				title: "用户",
				value: function(c, r) {
					c.innerHTML = r.vc_user;
				}
			},
			{
				title: "创建日期",
				value: function(c, r) {
					c.innerHTML = r.dt_created;
				}
			},
			{
				title: "",
				value: function(c, r) {
					showCommand(c, r, conf);
				} 
			}
		];
		newDBGrid(records, headers, "100%", panel);
		commitUpdateFieldContent();
	}
}

function showCommand(c, r, conf) {
	if(Number(r.tiu_state) == 0) {
		var start_btn = newInputElement('button', '', '开启');
		c.appendChild(start_btn);
		start_btn.rec = r;
		start_btn.conf = conf;
		start_btn.__destruct = function() {
			this.rec = null;
			this.conf = null;
		};
		EventManager.Add(start_btn, 'click', function(ev, obj) {
			updatePanelContentRPC(obj.conf.name, 'START_VMGUEST', {_id: obj.rec.id, _vc_user: _session_user});
		});
		/*
		var cd_start_btn = newInputElement('button', '', '光驱启动');
		c.appendChild(cd_start_btn);
		cd_start_btn.rec = r;
		cd_start_btn.conf = conf;
		cd_start_btn.__destruct = function() {
			this.rec = null;
			this.conf = null;
		};
		EventManager.Add(cd_start_btn, 'click', function(ev, obj) {
			updatePanelContentRPC(obj.conf.name, 'START_VMGUEST', {_host_id: obj.rec.host_id, _vc_name: obj.rec.guest_name, _vc_user: _session_user, _bootcd: 1});
		});
		*/
		var edit_btn = newInputElement('button', '', '修改');
		c.appendChild(edit_btn);
		edit_btn.rec = r;
		edit_btn.conf = conf;
		edit_btn.__destruct = function() {
			this.rec = null;
			this.conf = null;
		};
		EventManager.Add(edit_btn, 'click', function(ev, obj) {
			var config_div = getContentPanelConfigArea(obj.conf);
			editVMGuestRecord(obj.conf, config_div, obj.rec);
		});
		var edit_net_btn = newInputElement('button', '', '配置网络和存储');
		c.appendChild(edit_net_btn);
		edit_net_btn.rec = r;
		edit_net_btn.conf = conf;
		edit_net_btn.__destruct = function() {
			this.rec = null;
			this.conf = null;
		};
		EventManager.Add(edit_net_btn, 'click', function(ev, obj) {
			var frame= getVMGuestEditFrame(obj.rec.id, obj.rec.vc_name, obj.rec.host_id);
			var tbl = newTableElement('100%', 0, 0, 0, '', 2, 2, 'center', 'top', frame);
			tblCell(tbl, 1, 0).id = '_vm_guest_net_portmap_config';
			var div = tblCell(tbl, 0, 0);
			div.id = '_vm_guest_net_detailed_config';
			div.style.padding = '8px';
			makeContentPanel(div, {
				frame_scheme: 0,
				expand: 'none',
				title: '虚机' + obj.rec.vc_name + '网络设置',
				content_func: showVMGuestNics,
				menus: [{'txt':'新增', 'url': 'images/addnewitem.png'}],
				config_func: addVMGuestNic,
				query_vars: "a.id, b.vc_name as net_name, a.vc_addr, a.vc_mac, a.vc_model, a.iu_sndbuf",
				query_tables: "vm_guest_net_tbl a, vm_network_tbl b",
				query_conditions: "a.net_id=b.id AND a.guest_id=" + obj.rec.id,
				query_order: "a.dt_created asc"
			});
			var div = tblCell(tbl, 0, 1);
			div.id = '_vm_guest_disk_detailed_config';
			div.style.padding = '8px';
			makeContentPanel(div, {
				frame_scheme: 0,
				expand: 'none',
				title: '虚机' + obj.rec.vc_name + '硬盘设置',
				content_func: showVMGuestDisks,
				menus: [{'txt':'新增', 'url': 'images/addnewitem.png'}],
				config_func: addVMGuestDisk,
				query_vars: "id, siu_disk_sz, vc_if, vc_cache, vc_aio",
				query_tables: "vm_guest_disk_tbl",
				query_conditions: "guest_id=" + obj.rec.id,
				query_order: "dt_created asc"
			});
		});
		var del_btn = newInputElement('button', '', '删除');
		c.appendChild(del_btn);
		del_btn.rec = r;
		del_btn.conf = conf;
		del_btn.__destruct = function() {
			this.rec = null;
			this.conf = null;
		};
		EventManager.Add(del_btn, 'click', function(ev, obj) {
			showConfirm({
				msg: '是否确定删除?',
				onOK: function() { updatePanelContentRPC(obj.conf.name, 'DEL_VMGUEST', {_id: obj.rec.id, _vc_user: _session_user}); return true;},
				onCancel: function() {return true;}
			});
		});
	}else {
		var edit_btn = newInputElement('button', '', '停止');
		c.appendChild(edit_btn);
		edit_btn.rec = r;
		edit_btn.conf = conf;
		edit_btn.__destruct = function() {
			this.rec = null;
			this.conf = null;
		};
		EventManager.Add(edit_btn, 'click', function(ev, obj) {
			showConfirm({
				msg: '确定强行停止虚拟机?',
				onOK: function() { updatePanelContentRPC(obj.conf.name, 'STOP_VMGUEST', {_id: obj.rec.id, _vc_user: _session_user}); return true;},
				onCancel: function() {return true;}
			});
		});
		var start_vnc_btn = newInputElement('button', '', '启用VNC');
		c.appendChild(start_vnc_btn);
		start_vnc_btn.rec = r;
		start_vnc_btn.conf = conf;
		start_vnc_btn.__destruct = function() {
			this.rec = null;
			this.conf = null;
		};
		EventManager.Add(start_vnc_btn, 'click', function(ev, obj) {
			showConfirm({
				msg: '确定开启VNC端口?',
				onOK: function() { ajaxRPC('openVNCConsole', {_id: obj.rec.id}, onOpenVPNSuccess); return true;},
				onCancel: function() {return true;}
			});
		});
	}
}

function onOpenVPNSuccess(result) {
	var url = document.location.href;
	var hostname = document.location.hostname;
	if (hostname.indexOf(':') > 0) {
		hostname = hostname.substring(0, hostname.indexOf(':'));
	}
	url = url.substring(0, url.indexOf('/kvmadmin/')) + "/noVNC/vnc_auto.html?host=" + hostname + "&port=55900&encrypt=0&true_color=1";
	PopupManager.open(url, 800, 600, '_guest_list');
}

var __current_vm_guest_id = null;
var __current_vm_guest_name = null;
function getVMGuestEditFrame(guest_id, guest_name, host_id) {
	__current_vm_host_id    = host_id;
        __current_vm_guest_id   = guest_id;
        __current_vm_guest_name = guest_name;
        var frame = document.getElementById('_vm_guest_detailed_edit_pane');
        frame.align = 'center';
        removeAllChildNodes(frame);
        var cancel_btn = newInputElement('button', '', '取消');
        frame.appendChild(cancel_btn);
        EventManager.Add(cancel_btn, 'click', function(ev, obj) {
                var frame = document.getElementById('_vm_guest_detailed_edit_pane');
                removeAllChildNodes(frame);
		redrawPanelContent('_guest_list', true, false);
        });
        return frame;
}

function addVMGuestRecordValidate(form) {
	with(form) {
		if(!checkDOMUser(_vc_name, '虚拟机名不能为空，且只包含字母和数字！', true)) {
			return false;
		}
	}
	return true;
}

function addVMGuestRecord(conf, menuText, panel) {
	editVMGuestRecord(conf, panel);
}

function editVMGuestRecord(conf, panel, rec) {
	if(_vm_host_records == null || _vm_host_records.length == 0) {
		panel.appendChild(newParagraph('没有物理主机供创建虚拟机！'));
		panel.appendChild(newCancelConfigButton(conf));
		return;
	}

	if('undefined' === typeof(rec)) {
		var action = 'ADD_VMGUEST';
		var btn_txt = '添加';
		rec = {host_id: 0, guest_name: '', siu_memory: 256, vc_iso: ''};
	}else {
		var action = 'EDIT_VMGUEST';
		var btn_txt = '修改';
	}

	var form = newDefaultRPCForm(panel, action, conf.name, addVMGuestRecordValidate);
	form.appendChild(newInputElement('hidden', '_vc_user', _session_user));

	var tbl = newTableElement('', 0, 0, 2, '', 8, 2, ['right', 'left'], 'middle', form);

	tblCell(tbl, 0, 0).innerHTML = '物理主机：';
	if(action == 'EDIT_VMGUEST') {
		form.appendChild(newInputElement('hidden', '_id', rec.id));
		for(var i = 0; i < _vm_host_records.length; i ++) {
			if(_vm_host_records[i].id == rec.host_id) {
				var host_sel = newParagraph(_vm_host_records[i].vc_name);
				break;
			}
		}
	}else {
		var host_options = Array();
		for(var i = 0; i < _vm_host_records.length; i ++) {
			host_options[i] = {txt: _vm_host_records[i].vc_name, val: _vm_host_records[i].id};
		}
		var host_sel = newSelector(host_options, rec.host_id, '_host_id');
		host_sel.rec = rec;
		host_sel.__destructor = function() {
			this.rec = null;
		};
		EventManager.Add(host_sel, 'change', function(ev, obj) {
			var host_id = obj.options[obj.selectedIndex].value;
			updateHostISOList(host_id, obj.rec.vc_iso);
			updateHostNetworkList(tblCell(tbl, 5, 1), host_id);
		});
	}
	tblCell(tbl, 0, 1).appendChild(host_sel);

	tblCell(tbl, 1, 0).innerHTML = '虚拟机名：';
	var vm_name_txt = newInputElement('text', '_vc_name', rec.vc_name);
	tblCell(tbl, 1, 1).appendChild(vm_name_txt);

	tblCell(tbl, 2, 0).innerHTML = '内存容量(MB)：';
	tblCell(tbl, 2, 1).appendChild(newSelector(makeNumberRange(256, 2048, 256), rec.siu_memory, '_siu_memory'));

	tblCell(tbl, 3, 0).innerHTML = '启动介质：';
	var bootorder_opt = [{'txt': '硬盘', 'val': 'c'}, {'txt': '光驱', 'val': 'd'}, {'txt': '网络', 'val': 'n'}];
	var bootorder_sel = newSelector(bootorder_opt, rec.vc_bootorder, '_vc_bootorder');
	tblCell(tbl, 3, 1).appendChild(bootorder_sel);

	if(action == 'EDIT_VMGUEST') {
	}else {
		diskEditCells(tblCell(tbl, 4, 0), tblCell(tbl, 4, 1));

		var cur_host_id = host_sel.options[host_sel.selectedIndex].value;
		networkEditCells(tblCell(tbl, 5, 0), tblCell(tbl, 5, 1), cur_host_id);
	}

	tblCell(tbl, 6, 0).innerHTML = '挂载iso镜像：';
	tblCell(tbl, 6, 1).id = 'host_iso_list';
	if(action == 'EDIT_VMGUEST') {
		updateHostISOList(rec.host_id, rec.vc_iso);
	}else {
		var cur_host_id = host_sel.options[host_sel.selectedIndex].value;
		updateHostISOList(cur_host_id, rec.vc_iso);
	}

	tblCell(tbl, 7, 1).appendChild(newInputElement('submit', '', btn_txt));
	tblCell(tbl, 7, 1).appendChild(newCancelConfigButton(conf));
}

function diskEditCells(leftcell, rightcell) {
	var disk_mode_opt = [{'txt': '新建硬盘(GB)：', 'val': 'new'}, {'txt': '使用硬盘模板：', 'val': 'template'}];
	var disk_mode_sel = newSelector(disk_mode_opt, 'new', '_disk_mode');
	leftcell.appendChild(disk_mode_sel);

	EventManager.Add(disk_mode_sel, 'change', function(ev, obj) {
		onDiskModeChange(obj);
	});
	rightcell.id = '_disk_mode_cell';
	onDiskModeChange(disk_mode_sel);
}

function networkEditCells(leftcell, rightcell, host_id) {
	leftcell.innerHTML = '网络：';
	updateHostNetworkList(rightcell, host_id);
}

function onDiskModeChange(selobj) {
	var cell = document.getElementById('_disk_mode_cell');
	if (cell != null) {
		removeAllChildNodes(cell);
		if (selobj.selectedIndex == 0) {
			cell.appendChild(newInputElement('text', '_siu_disk_sz', 8));
		}else {
			var tmp_opts = Array();
			for(var i = 0; i < _vm_template_records.length; i ++) {
				tmp_opts[tmp_opts.length] = {'txt': _vm_template_records[i].name, 'val': _vm_template_records[i].id};
			}
			var tmp_sel = newSelector(tmp_opts, '', '_template_id');
			cell.appendChild(tmp_sel);
		}
	}
}

function updateHostISOList(host_id, cur_iso) {
	var cell = document.getElementById('host_iso_list');
	if(cell != null) {
		updateFieldContent(cell, 'host_iso_list', 'GET_ISO_LIST', {_host_id: host_id, _cur_iso: cur_iso}, onGetISOListSucc);
		commitUpdateFieldContent();
	}
}

function updateHostNetworkList(cell, host_id) {
	cell.id = 'host_network_list';
	updateFieldContent(cell, 'host_network_list', 'GET_HOST_NET_LIST', {_host_id: host_id}, onGetHostNetworkListSucc);
	commitUpdateFieldContent();
}

function onGetHostNetworkListSucc(cell, rec, params) {
	var net_sel = newSelector(rec._net_list, '', '_net_id');
	cell.appendChild(net_sel);
}

function onGetISOListSucc(cell, rec, params) {
	var iso_opt = Array();
	iso_opt[0] = {'txt': '无', 'val': ''};
	for(var i = 0; i < rec._iso_list.length; i ++) {
		iso_opt[iso_opt.length] = {'txt': rec._iso_list[i], 'val': rec._iso_list[i]};
	}
	var iso_sel = newSelector(iso_opt, params._cur_iso, '_vc_iso');
	cell.appendChild(iso_sel);
}

function showVMGuestNics(records, panel, conf) {
	if (records.length == 0) {
		panel.innerHTML = '没有网卡！';
	}else {
		var headers = [
		{
			title: '#',
			value: function(c, r) {
				c.innerHTML = r.id;
			}
		},
		{
			title: '网络',
			value: function(c, r) {
				c.innerHTML = r.net_name;
			}
		},
		{
			title: 'MAC',
			value: function(c, r) {
				c.innerHTML = r.vc_mac;
			}
		},
		{
			title: 'IP',
			value: function(c, r) {
				c.innerHTML = r.vc_addr;
			}
		},
		{
			title: "型号",
			value: function(c, r) {
				c.innerHTML = r.vc_model;
			}
		},
		{
			title: "发送缓存(KB)",
			value: function(c, r) {
				c.innerHTML = Number(r.iu_sndbuf)/1024;
			}
		},
		{
			title: '',
			value: function(c, r) {
				var edit_btn = newInputElement('button', '', '修改');
				c.appendChild(edit_btn);
				edit_btn.rec = r;
				edit_btn.conf = conf;
				edit_btn.__destructor = function() {
					this.conf = null;
					this.rec  = null;
				};
				EventManager.Add(edit_btn, 'click', function(ev, obj) {
					var config_div = getContentPanelConfigArea(obj.conf);
					editVMGuestNIC(obj.conf, config_div, obj.rec);
				});
				var del_btn = newInputElement('button', '', '删除');
				c.appendChild(del_btn);
				del_btn.rec = r;
				del_btn.conf = conf;
				del_btn.__destructor = function() {
					this.rec = null;
					this.conf = null;
				};
				EventManager.Add(del_btn, 'click', function(ev, obj) {
					showConfirm({
						msg: '是否确定删除?',
						onOK: function() { updatePanelContentRPC(obj.conf.name, 'DEL_VMGUEST_NIC', {_id: obj.rec.id}); return true;},
						onCancel: function() {return true;}
					});
				});
				var portmap_btn = newInputElement('button', '', '配置端口映射');
				c.appendChild(portmap_btn);
				portmap_btn.rec = r;
				portmap_btn.conf = conf;
				portmap_btn.__destruct = function() {
					this.rec = null;
					this.conf = null;
				};
				EventManager.Add(portmap_btn, 'click', function(ev, obj) {
					onEditNetworkPortMap(obj.conf, obj.rec);
				});
			}
		}];
		newDBGrid(records, headers, '100%', panel);
		///redrawPanelContent('_guest_list', true, false);
	}
}

function editVMGuestNIC(conf, config_div, rec) {
	var form = newDefaultRPCForm(config_div, 'EDIT_VMGUEST_NIC', conf.name);
	form.appendChild(newInputElement('hidden', '_id', rec.id));
	var tbl = newTableElement('', 0, 0, 2, '', 6, 2, ['right', 'left'], 'top', form);
	tblCell(tbl, 0, 0).innerHTML = '网卡#id：';
	tblCell(tbl, 0, 1).innerHTML = rec.id;
	tblCell(tbl, 1, 0).innerHTML = 'MAC：';
	tblCell(tbl, 1, 1).innerHTML = rec.vc_mac;
	tblCell(tbl, 2, 0).innerHTML = 'IP：';
	tblCell(tbl, 2, 1).innerHTML = rec.vc_addr;
	tblCell(tbl, 3, 0).innerHTML = '网卡型号：';
	var model_sel = newSelector(['virtio', 'i82551', 'i82559er','rtl8139','e1000'], rec.vc_model, '_vc_model');
	tblCell(tbl, 3, 1).appendChild(model_sel);
	tblCell(tbl, 4, 0).innerHTML = '发送缓存大小(KB)：';
	var sndbuf_sel = newSelector([{'txt': '0', 'val': '0'}, {'txt': '128k', 'val': 128*1024}, {'txt': '512k', 'val': 512*1024}, {'txt': '1M', 'val': 1024*1024}], rec.iu_sndbuf, '_iu_sndbuf');
	tblCell(tbl, 4, 1).appendChild(sndbuf_sel);
	tblCell(tbl, 5, 1).appendChild(newInputElement('submit', '', '保存'));
	tblCell(tbl, 5, 1).appendChild(newCancelConfigButton(conf));
}

var __current_guest_net_id = null;
function onEditNetworkPortMap(conf, rec) {
	__current_guest_net_id = rec.id;
	var div = document.getElementById('_vm_guest_net_portmap_config');
	div.style.padding = '8px';
	makeContentPanel(div, {
		frame_scheme: 0,
		expand: 'none',
		title: '虚机网卡#' + rec.id + '端口映射设置',
		content_func: showVMGuestNetworkPortConfig,
		menus: [{'txt':'新增', 'url': 'images/addnewitem.png'}],
		config_func: addVMGuestNetworkPortConfig,
		query_vars: "id, vc_protocol, siu_pub_port, siu_pri_port",
		query_tables: "vm_guest_net_portmap_tbl",
		query_conditions: "guest_net_id=" + rec.id,
		query_order: "dt_created asc"
	});
}

function addVMGuestNetworkPortConfigValidate(form) {
	with(form) {
		var port = Number(_siu_pub_port.value);
		if (port <= 0 || port >= 50000) {
			showTips(_siu_pub_port, '外部端口为1~49999整数');
			return false;
		}
		port = Number(_siu_pri_port.value);
		if (port <= 0 || port >= 50000) {
			showTips(_siu_pri_port, '内部端口为1~49999整数');
			return false;
		}
	}
	return true;
}

function addVMGuestNetworkPortConfig(conf, menuText, panel) {
	var form = newDefaultRPCForm(panel, 'ADD_VMGUEST_NETPORT_MAP', conf.name, addVMGuestNetworkPortConfigValidate);
	if (__current_guest_net_id != null && _vm_guest_net_records == null) {
		form.appendChild(newInputElement('hidden', '_guest_net_id', __current_guest_net_id));
	}
	var tbl = newTableElement('', 0, 0, 2, '', 5, 2, ['right', 'left'], 'top', form);
	if (__current_guest_net_id == null && _vm_guest_net_records != null) {
		tblCell(tbl, 0, 0).innerHTML = '虚拟机网络：';
		tblCell(tbl, 0, 1).appendChild(newSelector(_vm_guest_net_records, '', '_guest_net_id'));
	}
	tblCell(tbl, 1, 0).innerHTML = '协议：';
	var proto_sel = newSelector(['tcp', 'udp'], '', '_vc_protocol');
	tblCell(tbl, 1, 1).appendChild(proto_sel);
	tblCell(tbl, 2, 0).innerHTML = '外部端口：';
	tblCell(tbl, 2, 1).appendChild(newInputElement('text', '_siu_pub_port', '0'));
	tblCell(tbl, 3, 0).innerHTML = '内部端口：';
	tblCell(tbl, 3, 1).appendChild(newInputElement('text', '_siu_pri_port', '0'));
	tblCell(tbl, 4, 1).appendChild(newInputElement('submit', '', '保存'));
	tblCell(tbl, 4, 1).appendChild(newCancelConfigButton(conf));
}

function showVMGuestNetworkPortConfig(records, panel, conf) {
	if (records.length == 0) {
		panel.innerHTML = '没有配置端口映射！';
	}else {
		var headers = [
		{
			title: "#",
			value: function(c, r) {
				c.innerHTML = r.id;
			}
		},
		{
			title: "协议",
			value: function(c, r) {
				c.innerHTML = r.vc_protocol;
			}
		},
		{
			title: "外网端口",
			value: function(c, r) {
				c.innerHTML = r.siu_pub_port;
			}
		},
		{
			title: "内网端口",
			value: function(c, r) {
				c.innerHTML = r.siu_pri_port;
			}
		},
		{
			title: '',
			value: function(c, r) {
				var del_btn = newInputElement('button', '', '删除');
				c.appendChild(del_btn);
				del_btn.rec = r;
				del_btn.conf = conf;
				del_btn.__destruct = function() {
					this.rec = null;
					this.conf = null;
				};
				EventManager.Add(del_btn, 'click', function(ev, obj) {
					showConfirm({
						msg: '是否确定删除?',
						onOK: function() { updatePanelContentRPC(obj.conf.name, 'DEL_VMGUEST_NETPORT_MAP', {_id: obj.rec.id}); return true;},
						onCancel: function() {return true;}
					});
				});
			}
		}
		];
		newDBGrid(records, headers, "100%", panel);
	}
}

function addVMGuestNic(conf, menuText, panel) {
	var form = newDefaultRPCForm(panel, 'ADD_VMGUEST_NIC', conf.name);
	form.appendChild(newInputElement('hidden', '_guest_id', __current_vm_guest_id));
	var tbl = newTableElement('', 0, 0, 2, '', 4, 2, ['right', 'left'], 'top', form);
	networkEditCells(tblCell(tbl, 0, 0), tblCell(tbl, 0, 1), __current_vm_host_id);
	tblCell(tbl, 3, 1).appendChild(newInputElement('submit', '', '保存'));
	tblCell(tbl, 3, 1).appendChild(newCancelConfigButton(conf));
}

function showVMGuestDisks(records, panel, conf) {
	if (records.length == 0) {
		panel.innerHTML = '没有硬盘！';
	}else {
		var headers = [
		{
			title: "#",
			value: function(c, r) {
				c.innerHTML = r.id;
			}
		},
		{
			title: "尺寸(GB)",
			value: function(c, r) {
				c.innerHTML = r.siu_disk_sz;
			}
		},
		{
			title: "IF",
			value: function(c, r) {
				c.innerHTML = r.vc_if;
			}
		},
		{
			title: "Cache",
			value: function(c, r) {
				c.innerHTML = r.vc_cache;
			}
		},
		{
			title: "AIO",
			value: function(c, r) {
				c.innerHTML = r.vc_aio;
			}
		},
		{
			title: '',
			value: function(c, r) {
				var edit_btn = newInputElement('button', '', '修改');
				c.appendChild(edit_btn);
				edit_btn.rec = r;
				edit_btn.conf = conf;
				edit_btn.__destructor = function() {
					this.conf = null;
					this.rec  = null;
				};
				EventManager.Add(edit_btn, 'click', function(ev, obj) {
					var config_div = getContentPanelConfigArea(obj.conf);
					editVMGuestDisk(obj.conf, config_div, obj.rec);
				});
				var del_btn = newInputElement('button', '', '删除');
				c.appendChild(del_btn);
				del_btn.rec = r;
				del_btn.conf = conf;
				del_btn.__destruct = function() {
					this.rec = null;
					this.conf = null;
				};
				EventManager.Add(del_btn, 'click', function(ev, obj) {
					showConfirm({
						msg: '是否确定删除?',
						onOK: function() { updatePanelContentRPC(obj.conf.name, 'DEL_VMGUEST_DISK', {_id: obj.rec.id}); return true;},
						onCancel: function() {return true;}
					});
				});
				var save_btn = newInputElement('button', '', '保存为模板');
				c.appendChild(save_btn);
				save_btn.rec = r;
				save_btn.conf = conf;
				save_btn.__destruct = function() {
					this.rec = null;
					this.conf = null;
				};
				EventManager.Add(save_btn, 'click', function(ev, obj) {
					/*showConfirm({
						msg: '是否确定将此磁盘保存为模板?',
						onOK: function() { updatePanelContentRPC(obj.conf.name, 'SAVE_VMGUEST_DISK_TEMPLATE', {_id: obj.rec.id}); return true;},
						onCancel: function() {return true;}
					});*/
					var config_div = getContentPanelConfigArea(obj.conf);
					saveDiskAsTemplate(obj.conf, config_div, obj.rec.id);
				});
			}
		}
		];
		newDBGrid(records, headers, "100%", panel);
		//redrawPanelContent('_guest_list', true, false);
	}
}

function editVMGuestDisk(conf, config_div, rec) {
	var form = newDefaultRPCForm(config_div, 'EDIT_VMGUEST_DISK', conf.name);
	form.appendChild(newInputElement('hidden', '_id', rec.id));
	var tbl = newTableElement('', 0, 0, 2, '', 5, 2, ['right', 'left'], 'top', form);
	tblCell(tbl, 0, 0).innerHTML = '硬盘#id：';
	tblCell(tbl, 0, 1).innerHTML = rec.id;
	tblCell(tbl, 1, 0).innerHTML = '接口：';
	var if_sel = newSelector(['virtio', 'ide', 'scsi', 'sd', 'mtd', 'floppy', 'pflash'], rec.vc_if, '_vc_if');
	tblCell(tbl, 1, 1).appendChild(if_sel);
	tblCell(tbl, 2, 0).innerHTML = '缓存模式：';
	var cache_sel = newSelector(['none', 'writeback', 'writethrough'], rec.vc_cache, '_vc_cache');
	tblCell(tbl, 2, 1).appendChild(cache_sel);
	tblCell(tbl, 3, 0).innerHTML = 'AIO：';
	var aio_sel = newSelector(['native', 'threads'], rec.vc_aio, '_vc_aio');
	tblCell(tbl, 3, 1).appendChild(aio_sel);
	tblCell(tbl, 4, 1).appendChild(newInputElement('submit', '', '保存'));
	tblCell(tbl, 4, 1).appendChild(newCancelConfigButton(conf));
}

function onAddVMGuestDiskValidate(form) {
	with(form) {
	}
	return true;
}

function addVMGuestDisk(conf, menuText, panel) {
	var form = newDefaultRPCForm(panel, 'ADD_VMGUEST_DISK', conf.name, onAddVMGuestDiskValidate);
	form.appendChild(newInputElement('hidden', '_guest_id', __current_vm_guest_id));
	var tbl = newTableElement('', 0, 0, 2, '', 4, 2, ['right', 'left'], 'top', form);
	diskEditCells(tblCell(tbl, 0, 0), tblCell(tbl, 0, 1));
	tblCell(tbl, 3, 1).appendChild(newInputElement('submit', '', '保存'));
	tblCell(tbl, 3, 1).appendChild(newCancelConfigButton(conf));
}

function saveDiskAsTemplate(conf, panel, disk_id) {
	editTemplateRecord(conf, panel, {disk_id: disk_id, vc_name: '', vc_os: '', vc_version: '', tiu_bits: 64, vc_lang: ''}, 'SAVE_VMGUEST_DISK_TEMPLATE', '添加');
}
