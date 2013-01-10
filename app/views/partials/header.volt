<div class="navbar-inner">
	<div class="container">
		<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</a>

		{% if currentUser %}
			<a href="{{ url('dashboard/index/') }}" class="brand">Project Manager</a>
		{% else %}
			<a href="{{ url('user/login/') }}" class="brand">Project Manager</a>
		{% endif %}

		<div class="nav-collapse collapse">
			{% if currentUser %}
			<ul class="nav pull-left">
				<li {% if controller == "dashboard" %} class="active" {% endif %}>
					<a href="{{ url('dashboard/index') }}">
						<i class="icon-home icon-white"></i>
					</a>
				</li>
			</ul>
			{% endif %}

			<ul class="nav pull-right">
				{% if currentUser %}
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						Masood Ahmed
					</a>

					<ul class="dropdown-menu">
						<li>
							<a href="#">
								<i class="icon-user"></i>
								My Account
							</a>
						</li>
						<li>
							<a href="#">
								<i class="icon-cog"></i>
								Admin
							</a>
						</li>
						<li class="divider"></li>
						<li>
							<a href="#">
								<i class="icon-off"></i>
								Logout
							</a>
						</li>
					</ul>
				</li>
				{% else %}
					<li class="active">
						<a href="{{ url('user/login/') }}">
							Log in
						</a>
					</li>
				{% endif %}
			</ul>
		</div> <!-- .nav-collapse -->
	</div> <!-- .container -->
</div> <!-- .navbar-inner -->
