# Oracle houses RPMS behind a developer agreement
# We host them on the UCSF box account and then download them when the box is built
# This is necessary becuase git does not handle large binaries well and github rejects them outright
# If these files need to be updated contact jazzee@ucsf.edu for access.
mkdir -p /opt/oracle-rpms
wget https://ucsf.box.com/shared/static/ipjwkbse25t4l4esyevl.rpm -O /opt/oracle-rpms/oracle-instantclient11.2-devel-11.2.0.3.0-1.x86_64.rpm
wget https://ucsf.box.com/shared/static/o1w5sagi0ltks4j0hxqa.rpm -O /opt/oracle-rpms/oracle-instantclient11.2-sqlplus-11.2.0.3.0-1.x86_64.rpm
wget https://ucsf.box.com/shared/static/qc4z8fbbjipmpwht4gfm.rpm -O /opt/oracle-rpms/oracle-xe-11.2.0-1.0.x86_64.rpm
wget https://ucsf.box.com/shared/static/dbxarn90jcna65dne66q.rpm -O /opt/oracle-rpms/oracle-instantclient11.2-basic-11.2.0.3.0-1.x86_64.rpm