FROM fazy/apache-symfony
MAINTAINER Thurairajah Thujeevan <thujee@gmail.com>

RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get -y install mysql-server php5-mysql pwgen && \
  echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Add image configuration and scripts
ADD docker/run.sh /run.sh
RUN chmod 755 /*.sh
ADD docker/my.cnf /etc/mysql/conf.d/my.cnf

ADD docker/mysql_user_create.sh /create_mysql_sad_user_db.sh
RUN chmod 755 /*.sh

ADD docker/apache.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

CMD ["/run.sh"]