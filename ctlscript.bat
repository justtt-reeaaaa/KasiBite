@echo off
rem START or STOP Services
rem ----------------------------------
rem Check if argument is STOP or START

if not ""%1"" == ""START"" goto stop

if exist C:\xampp\htdocs\KasiBite\hypersonic\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\server\hsql-sample-database\scripts\ctl.bat START)
if exist C:\xampp\htdocs\KasiBite\ingres\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\ingres\scripts\ctl.bat START)
if exist C:\xampp\htdocs\KasiBite\mysql\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\mysql\scripts\ctl.bat START)
if exist C:\xampp\htdocs\KasiBite\postgresql\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\postgresql\scripts\ctl.bat START)
if exist C:\xampp\htdocs\KasiBite\apache\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\apache\scripts\ctl.bat START)
if exist C:\xampp\htdocs\KasiBite\openoffice\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\openoffice\scripts\ctl.bat START)
if exist C:\xampp\htdocs\KasiBite\apache-tomcat\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\apache-tomcat\scripts\ctl.bat START)
if exist C:\xampp\htdocs\KasiBite\resin\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\resin\scripts\ctl.bat START)
if exist C:\xampp\htdocs\KasiBite\jetty\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\jetty\scripts\ctl.bat START)
if exist C:\xampp\htdocs\KasiBite\subversion\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\subversion\scripts\ctl.bat START)
rem RUBY_APPLICATION_START
if exist C:\xampp\htdocs\KasiBite\lucene\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\lucene\scripts\ctl.bat START)
if exist C:\xampp\htdocs\KasiBite\third_application\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\third_application\scripts\ctl.bat START)
goto end

:stop
echo "Stopping services ..."
if exist C:\xampp\htdocs\KasiBite\third_application\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\third_application\scripts\ctl.bat STOP)
if exist C:\xampp\htdocs\KasiBite\lucene\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\lucene\scripts\ctl.bat STOP)
rem RUBY_APPLICATION_STOP
if exist C:\xampp\htdocs\KasiBite\subversion\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\subversion\scripts\ctl.bat STOP)
if exist C:\xampp\htdocs\KasiBite\jetty\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\jetty\scripts\ctl.bat STOP)
if exist C:\xampp\htdocs\KasiBite\hypersonic\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\server\hsql-sample-database\scripts\ctl.bat STOP)
if exist C:\xampp\htdocs\KasiBite\resin\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\resin\scripts\ctl.bat STOP)
if exist C:\xampp\htdocs\KasiBite\apache-tomcat\scripts\ctl.bat (start /MIN /B /WAIT C:\xampp\htdocs\KasiBite\apache-tomcat\scripts\ctl.bat STOP)
if exist C:\xampp\htdocs\KasiBite\openoffice\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\openoffice\scripts\ctl.bat STOP)
if exist C:\xampp\htdocs\KasiBite\apache\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\apache\scripts\ctl.bat STOP)
if exist C:\xampp\htdocs\KasiBite\ingres\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\ingres\scripts\ctl.bat STOP)
if exist C:\xampp\htdocs\KasiBite\mysql\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\mysql\scripts\ctl.bat STOP)
if exist C:\xampp\htdocs\KasiBite\postgresql\scripts\ctl.bat (start /MIN /B C:\xampp\htdocs\KasiBite\postgresql\scripts\ctl.bat STOP)

:end

