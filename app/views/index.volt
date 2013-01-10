<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="Project Manager">
		<meta name="author" content="Masood Ahmed">

		<title>Project Manager - {{ get_title() }}</title>

		<style>
            body {
                padding-top: 40px;
            }
        </style>

        {{ stylesheet_link('css/bootstrap-responsive.css') }}
        {{ stylesheet_link('css/bootstrap-editable.css') }}
        {{ stylesheet_link('css/jquery.easy-pie-chart.css') }}
        {{ stylesheet_link('css/style.css') }}

        {% block head_css %}{% endblock %}
	</head>

	<body id="{{ body_id }}" class="{{ body_class }}">

        {{ javascript_include('js/jquery-1.8.3.min.js') }}
        {{ javascript_include('js/bootstrap.min.js') }}
        {{ javascript_include('js/bootstrap-editable.min.js') }}
        {{ javascript_include('js/jquery.easydate-0.2.4.min.js') }}
        {{ javascript_include('js/jquery.easy-pie-chart.js') }}

        {% block head_js %}{% endblock %}
    </body>
</html>
