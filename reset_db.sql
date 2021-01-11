delete from ansprechpartner;
alter table ansprechpartner auto_increment = 1;

delete from kunden;
alter table kunden auto_increment = 1;

delete from turnus;
alter table turnus auto_increment = 1;

delete from ersatzteil;
alter table ersatzteil auto_increment = 1;

delete from wartungen;
alter table wartungen auto_increment = 1;

delete from anlagen;
alter table anlagen auto_increment = 1;

delete from coordinate_data_cache;
delete from wartung_kosten;
