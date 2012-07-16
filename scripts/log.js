function showKvmLogs(records, panel, conf) {
	if(records.length == 0) {
		panel.innerHTML = "No records!";
	}else {
		var headers = [
			{
				title: "#",
				value: function(c, r) {
					c.innerHTML = r.id;
				}
			},
			{
				title: "时间",
				value: function(c, r) {
					c.innerHTML = r.dt_when;
				}
			},
			{
				title: "Host主机",
				value: function(c, r) {
					if(r.host_name === null) {
						c.innerHTML = "(Deleted/#" + r.host_id + ")";
					}else {
						c.innerHTML = r.host_name + "(#" + r.host_id + ")";
					}
				}
			},
			{
				title: "虚拟机",
				value: function(c, r) {
					c.innerHTML = r.guest_name;
				}
			},
			{
				title: "用户",
				value: function(c, r) {
					c.innerHTML = r.vc_user;
				}
			},
			{
				title: "日志",
				value: function(c, r) {
					c.innerHTML = r.notes;
				}
			}
		];
		newDBGrid(records, headers, "", panel);
	}
}
