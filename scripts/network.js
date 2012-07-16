function showNetworkPortMapList(records, panel, conf) {
	if (records.length == 0) {
		panel.innerHTML = '没有端口映射配置';
	}else {
		var headers = [
		{
			title: '#',
			value: function(c, r) {
				c.innerHTML = r.id;
			}
		},
		{
			title: "外部IP",
			value: function(c, r) {
				c.innerHTML = r.pub_name + " (" + r.vc_public_ip + ")";
			}
		},
		{
			title: "外部端口",
			value: function(c, r) {
				c.innerHTML = r.siu_pub_port;
			}
		},
		{
			title: "内部IP",
			value: function(c, r) {
				c.innerHTML = r.vc_name + " (" + r.vc_addr + ")";
			}
		},
		{
			title: "内部端口",
			value: function(c, r) {
				c.innerHTML = r.siu_pri_port;
			}
		},
		{
			title: "生效",
			value: function(c, r) {
				if (Number(r.tiu_state) == 1) {
					c.innerHTML = '是';
				}else {
					c.innerHTML = '否';
				}
			}
		},
		{
			title: "",
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

function showNetworkList(records, panel, conf) {
	if(records.length == 0) {
		panel.innerHTML = "没有配置网络!";
	}else {
		var headers = [
			{
				title: "#",
				value: function(c, r) {
					c.innerHTML = r.id;
				}
			},
			{
				title: "名称",
				value: function(c, r) {
					c.innerHTML = r.vc_name;
				}
			},
			{
				title: "网关地址",
				value: function(c, r) {
					c.innerHTML = r.vc_gateway;
				}
			},
			{
				title: "外部IP",
				value: function(c, r) {
					c.innerHTML = r.vc_public_ip;
				}
			},
			{
				title: "VLAN ID",
				value: function(c, r) {
					c.innerHTML = r.siu_vlan_id;
				}
			},
			{
				title: "地址池",
				value: function(c, r) {
					c.innerHTML = r.vc_addr_start + "~" + r.vc_addr_end + "(/" + r.tiu_netmask + ")";
				}
			}
		];
		if(_session_user === 'admin') {
			headers[headers.length] = {
				title: "",
				value: function(c, r) {
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
						editNetworkRecord(obj.conf, config_div, obj.rec);
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
							onOK: function() { updatePanelContentRPC(obj.conf.name, 'DEL_NETWORK', {_id: obj.rec.id}); return true;},
							onCancel: function() {return true;}
						});
					});
				}
			};
		}
		newDBGrid(records, headers, "100%", panel);
	}
}

function addNetworkRecordValidate(form) {
	with(form) {
		if(!checkDOMNonempty(_vc_name, '网络名称不能为空！')) {
			return false;
		}
		if (_siu_vlan_id.value.length == 0) {
			_siu_vlan_id.value = 0;
		}
		if (Number(_siu_vlan_id.value) < 0 || Number(_siu_vlan_id.value) > 4094) {
			showTips(_siu_vlan_id, 'VLAN ID必需为0~4094之间的整数！');
			return false;
		}
		if(!checkDOMIP(_vc_addr_start, '开始地址不能为空！', true)) {
			return false;
		}
		if(!checkDOMIP(_vc_addr_end, '结束地址不能为空！', true)) {
			return false;
		}
	}
	return true;
}

function addNetworkRecord(conf, menuText, panel) {
	editNetworkRecord(conf, panel);
}

function editNetworkRecord(conf, panel, rec) {
	if('undefined' === typeof rec) {
		var action = 'ADD_NETWORK';
		var btn_txt = '添加';
		rec = {id: 0, vc_name: '', vc_gateway: '', vc_public_ip: '', tiu_netmask: 24, vc_addr_start: '', vc_addr_end: ''};
	}else {
		var action = 'EDIT_NETWORK';
		var btn_txt = '修改';
	}

	var form = newDefaultRPCForm(panel, action, conf.name, addNetworkRecordValidate);
	form.appendChild(newInputElement('hidden', '_id', rec.id));

	var tbl = newTableElement('', 0, 0, 2, '', 8, 2, ['right', 'left'], 'middle', form);

	tblCell(tbl, 0, 0).innerHTML = '网络名称：';
	tblCell(tbl, 0, 1).appendChild(newInputElement('text', '_vc_name', rec.vc_name));
	tblCell(tbl, 1, 0).innerHTML = '网关地址：';
	tblCell(tbl, 1, 1).appendChild(newInputElement('text', '_vc_gateway', rec.vc_gateway));
	tblCell(tbl, 2, 0).innerHTML = '公网地址：';
	tblCell(tbl, 2, 1).appendChild(newInputElement('text', '_vc_public_ip', rec.vc_public_ip));
	tblCell(tbl, 3, 0).innerHTML = 'VLAN ID：';
	tblCell(tbl, 3, 1).appendChild(newInputElement('text', '_siu_vlan_id', rec.siu_vlan_id));
	tblCell(tbl, 4, 0).innerHTML = '子网掩码：';
	var netmask_opts = [{'txt': '255.255.255.224(/27)', 'val':27}, {'txt': '255.255.255.196(/26)', 'val':26}, {'txt': '255.255.255.128(/25)', 'val':25}, {'txt': '255.255.255.0(/24)', 'val':24}, {'txt':'255.255.254.0(/23)', 'val': 23}, {'txt': '255.255.252.0(/22)', 'val': 22}];
	tblCell(tbl, 4, 1).appendChild(newSelector(netmask_opts, rec.tiu_netmask, '_tiu_netmask'));
	tblCell(tbl, 5, 0).innerHTML = '开始地址：';
	tblCell(tbl, 5, 1).appendChild(newInputElement('text', '_vc_addr_start', rec.vc_addr_start));
	tblCell(tbl, 6, 0).innerHTML = '结束地址：';
	tblCell(tbl, 6, 1).appendChild(newInputElement('text', '_vc_addr_end', rec.vc_addr_end));

	tblCell(tbl, 7, 1).appendChild(newInputElement('submit', '', btn_txt));
	tblCell(tbl, 7, 1).appendChild(newCancelConfigButton(conf));
}

function networkPortmapConfig(conf, menuText, panel) {
	if (menuText == '添加') {
		addVMGuestNetworkPortConfig(conf, menuText, panel);	
	}else {
		syncNetworkPortmapConfig(conf, menuText, panel);
	}
}

function syncNetworkPortmapConfig(conf, menuText, panel) {
	hideContentConfigPanel(conf);
	showConfirm({
		msg: '确定和网关同步端口映射配置?',
		onOK: function() { ajaxRPC('syncNetworkPortmapConfig', {}, onSyncNetworkPortmapConfigSucc); return true;},
		onCancel: function() {return true;}
	});
}

function onSyncNetworkPortmapConfigSucc(result) {
	var msg = '';
	if (result.add == 0 && result.del == 0) {
		msg = '映射表没有变化.';
	}else {
		if (result.del > 0) {
			msg += '删除' + result.del + '条映射.';
		}
		if (result.add > 0) {
			msg += '新增' + result.add + '条映射.';
		}
	}
	showAlert(msg);
	redrawPanelContent('_network_port_map_list', true, false);
}
