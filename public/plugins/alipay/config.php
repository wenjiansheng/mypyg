<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2016100200646012",

		//商户私钥
		'merchant_private_key' => "MIIEpAIBAAKCAQEA0eF67A1lc+NphdxPsRjUKWmby19fPY0W9j3lxZSpAGco+Tzwhz51Qf5kOxlPuO8H05N4gYj6HwLzi8P12yXPI+QBLt55oawnODClvkIoDq3UUBJtMqN+OT9Do+6iB6G96DJisjQzWcbMlidDQABgjarHcodB0qBVPM39n7LydHZnR1lZ5ia2ira/ANC2JX/P/n1EqbfZQACU0pDdrUd+wCBogsQb8PhZAa2FZMgTVG/5OoZN/kVzxkMXNhLsf2P5KWPIxgWTvNN4R16VyVO6OmqHEjvd7VhGBCQ32oa0xXtAYPASoesVvKGkJwceDhFDSf3e3OwL/EL+nrx51qnRdwIDAQABAoIBAByosnVLOwBBHGan98XSAx6IijqSElpAStNjDl4Vd6JTc9fcx2dgUvio+RzMzwMMuL/eUkU15BmZ+JGsN4UuO4PGHZc9z9QQwuY8VlWNiRTADc7FRV31z0WX6u+WpU9veZQcVTfKQvzOVZ9nSxqRMp4FpOhxqb5OIHWGF0zYTZ0zUoCwQw0K/09mnXKoHS0Tt4QseYXBLynevl2JQfuNjuySAeFMI+pRBTFCUGjktWx1+lSZr8U453hUs1pU/9DFOt5U1yaI4F48z3Uqk4PlNbUtq8KzTNhUNi1HNlclt9eGas9gRyO1gtPL/l4gwh46aNmLgJ9GLczmUUWC6jH+H1ECgYEA7nNkBTP6UlY/1iFBehDdGlWmDRhsf5JruwVSduUGJMkXBbbI0Mq/wt09/JTfHSH8NwG0BS4dNWDnEnwZfYMszrO1+HQG48O+/rqFguwotHlpAuAFHO1BZdIOLx51JV8AcF3d6ZivyGCGojejtxdmVUxdSetUzQb5Y2aYBqxxNTkCgYEA4VPOxtPQNgXH3tpsQPAxo7nymiry0Lyo5bGtheldvnwWcjxRppARLkOS33vq9IEjJsHnTLTKQFzxub9tmbrgeEDhLWUwaIoGNXee9Ex0lM3eN7hgeaqN3VIr5oLL7p/iuwVMu1SQDEy4ZWWHnG34c3+w8KJT52t8hWW8YzvVbC8CgYEA3krka5VRP4D4cf7+s3UVKn++FWc3fpZZqDgF8KEBYTm2JLWJ8FuS+W6XYujGNrqADiegU1PyFtkHkcvvV3r6Qph2g/Bmm97YJ1BrNupB6D1nEC8XlSf5V5rDblnOi9BM8HQRdLvK49pYqNGF5iICXHKcZVTl8V0XEPuLrm0D/MECgYEApBfzeyqf8+er0eiBc504mUJ+obVrVYY+/d+yPdZgdatKcXDqCYHbcTS2zlghGGS/I6eMeXkj3VGSnDIDcDP+6rRdCEErysXRlbiCoujW8dMm5K86WwBRBf+kht90AliftpE6eyYvLTXmB7mz08Dmcb5pLiUZM2w8p60aT64wU/MCgYBLvRwu5j3k4A9JzCGd/WAeyyppk4XIpMfRqCOVJI/tKPHR1VFjhu5NsAjSAA+y8QMBsLZFPV09gfO029sSDP4u1pRNuT2gndQFIAP4ZTvaisyQRZAdCOXgg6DAEFFrK4kyntnHQKjOM1V71mEzGWujpnPz7lD3PPOXi42lmryqpA==",
		
		//异步通知地址
		'notify_url' => "http://www.soul.com/home/order/notify",
		
		//同步跳转
		'return_url' => "http://www.soul.com/home/order/callback",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApubTsQypbC4zLCqDLvpgBlr59+exoNahNnHZ1PYtOwYUEQ3coOnLJxiM0T0/pJDDo5MXbjAeW0/MBNex0sPnTbPmSvl56S1oHNQ5fhK2iIrPMQ4qTBI6U8UH4PCY5No3IWIF6ZQd81gyHbWmY3PFkTDwGuWTxacUVKfv02D0ghAhE026IORBDUJRvU5i2pNyj11nHT8ev4lNbdX7ZJoawTcWmJ+7TYAOB45YPdexFwjQhgDL8FrpNWtwPGwwsoVjc4xsEXDi3nGGLWf7zuB2lcftOHXlwBwIVnEWOSNnUft/cqMOmC++LNDrp/UI4Z/Kl3xwiZ//m6rkMV8uU0HsGwIDAQAB",
);