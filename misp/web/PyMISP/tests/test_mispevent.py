#!/usr/bin/env python
# -*- coding: utf-8 -*-

import unittest
import json
from io import BytesIO
import glob
import hashlib
from datetime import date, datetime

from pymisp import (MISPEvent, MISPSighting, MISPTag, MISPOrganisation,
                    MISPObject)
from pymisp.exceptions import InvalidMISPObject
from pymisp.tools import GitVulnFinderObject


class TestMISPEvent(unittest.TestCase):

    def setUp(self):
        self.maxDiff = None
        self.mispevent = MISPEvent()

    def init_event(self):
        self.mispevent.info = 'This is a test'
        self.mispevent.distribution = 1
        self.mispevent.threat_level_id = 1
        self.mispevent.analysis = 1
        self.mispevent.set_date("2017-12-31")  # test the set date method

    def test_simple(self):
        with open('tests/mispevent_testfiles/simple.json', 'r') as f:
            ref_json = json.load(f)
        del self.mispevent.uuid
        self.assertEqual(self.mispevent.to_json(sort_keys=True, indent=2), json.dumps(ref_json, sort_keys=True, indent=2))

    def test_event(self):
        self.init_event()
        self.mispevent.publish()
        with open('tests/mispevent_testfiles/event.json', 'r') as f:
            ref_json = json.load(f)
        del self.mispevent.uuid
        self.assertEqual(self.mispevent.to_json(sort_keys=True, indent=2), json.dumps(ref_json, sort_keys=True, indent=2))

    def test_loadfile(self):
        self.mispevent.load_file('tests/mispevent_testfiles/event.json')
        with open('tests/mispevent_testfiles/event.json', 'r') as f:
            ref_json = json.load(f)
        del self.mispevent.uuid
        self.assertEqual(self.mispevent.to_json(sort_keys=True, indent=2), json.dumps(ref_json, sort_keys=True, indent=2))

    def test_event_tag(self):
        self.init_event()
        self.mispevent.add_tag('bar')
        self.mispevent.add_tag(name='baz')
        new_tag = MISPTag()
        new_tag.from_dict(name='foo')
        self.mispevent.add_tag(new_tag)
        with open('tests/mispevent_testfiles/event_tags.json', 'r') as f:
            ref_json = json.load(f)
        del self.mispevent.uuid
        self.assertEqual(self.mispevent.to_json(sort_keys=True, indent=2), json.dumps(ref_json, sort_keys=True, indent=2))

    def test_attribute(self):
        self.init_event()
        a = self.mispevent.add_attribute('filename', 'bar.exe')
        del a.uuid
        a = self.mispevent.add_attribute_tag('osint', 'bar.exe')
        attr_tags = self.mispevent.get_attribute_tag('bar.exe')
        self.assertEqual(self.mispevent.attributes[0].tags[0].name, 'osint')
        self.assertEqual(attr_tags[0].name, 'osint')
        with open('tests/mispevent_testfiles/attribute.json', 'r') as f:
            ref_json = json.load(f)
        del self.mispevent.uuid
        self.assertEqual(self.mispevent.to_json(sort_keys=True, indent=2), json.dumps(ref_json, sort_keys=True, indent=2))
        # Fake setting an attribute ID for testing
        self.mispevent.attributes[0].id = 42
        self.mispevent.delete_attribute(42)
        with open('tests/mispevent_testfiles/attribute_del.json', 'r') as f:
            ref_json = json.load(f)
        self.assertEqual(self.mispevent.to_json(sort_keys=True, indent=2), json.dumps(ref_json, sort_keys=True, indent=2))

    def test_object_tag(self):
        self.mispevent.add_object(name='file', strict=True)
        a = self.mispevent.objects[0].add_attribute('filename', value='')
        self.assertEqual(a, None)
        a = self.mispevent.objects[0].add_attribute('filename', value=None)
        self.assertEqual(a, None)
        a = self.mispevent.objects[0].add_attribute('filename', value='bar', Tag=[{'name': 'blah'}])
        del a.uuid
        self.assertEqual(self.mispevent.objects[0].attributes[0].tags[0].name, 'blah')
        self.assertTrue(self.mispevent.objects[0].has_attributes_by_relation(['filename']))
        self.assertEqual(len(self.mispevent.objects[0].get_attributes_by_relation('filename')), 1)
        self.mispevent.add_object(name='url', strict=True)
        a = self.mispevent.objects[1].add_attribute('url', value='https://www.circl.lu')
        del a.uuid
        self.mispevent.objects[0].uuid = 'a'
        self.mispevent.objects[1].uuid = 'b'
        reference = self.mispevent.objects[0].add_reference(self.mispevent.objects[1], 'baz', comment='foo')
        del reference.uuid
        self.assertEqual(self.mispevent.objects[0].references[0].relationship_type, 'baz')
        with open('tests/mispevent_testfiles/event_obj_attr_tag.json', 'r') as f:
            ref_json = json.load(f)
        del self.mispevent.uuid
        self.assertEqual(self.mispevent.to_json(sort_keys=True, indent=2), json.dumps(ref_json, sort_keys=True, indent=2))

    @unittest.skip("Not supported on MISP: https://github.com/MISP/MISP/issues/2638 - https://github.com/MISP/PyMISP/issues/168")
    def test_object_level_tag(self):
        self.mispevent.add_object(name='file', strict=True)
        self.mispevent.objects[0].add_attribute('filename', value='bar')
        self.mispevent.objects[0].add_tag('osint')
        self.mispevent.objects[0].uuid = 'a'
        with open('tests/mispevent_testfiles/event_obj_tag.json', 'r') as f:
            ref_json = json.load(f)
        self.assertEqual(self.mispevent.to_json(sort_keys=True, indent=2), json.dumps(ref_json, sort_keys=True, indent=2))

    def test_malware(self):
        with open('tests/mispevent_testfiles/simple.json', 'rb') as f:
            pseudofile = BytesIO(f.read())
        self.init_event()
        a = self.mispevent.add_attribute('malware-sample', 'bar.exe', data=pseudofile)
        del a.uuid
        attribute = self.mispevent.attributes[0]
        self.assertEqual(attribute.malware_binary, pseudofile)
        with open('tests/mispevent_testfiles/malware.json', 'r') as f:
            ref_json = json.load(f)
        del self.mispevent.uuid
        self.assertEqual(self.mispevent.to_json(sort_keys=True, indent=2), json.dumps(ref_json, sort_keys=True, indent=2))

    def test_existing_malware(self):
        self.mispevent.load_file('tests/mispevent_testfiles/malware_exist.json')
        with open('tests/mispevent_testfiles/simple.json', 'rb') as f:
            pseudofile = BytesIO(f.read())
        self.assertEqual(
            self.mispevent.objects[0].get_attributes_by_relation('malware-sample')[0].malware_binary.read(),
            pseudofile.read())

    def test_sighting(self):
        sighting = MISPSighting()
        sighting.from_dict(value='1', type='bar', timestamp=11111111)
        with open('tests/mispevent_testfiles/sighting.json', 'r') as f:
            ref_json = json.load(f)
        self.assertEqual(sighting.to_json(sort_keys=True, indent=2), json.dumps(ref_json, sort_keys=True, indent=2))

    def test_existing_event(self):
        self.mispevent.load_file('tests/mispevent_testfiles/existing_event.json')
        with open('tests/mispevent_testfiles/existing_event.json', 'r') as f:
            ref_json = json.load(f)

        self.assertEqual(self.mispevent.to_json(sort_keys=True, indent=2), json.dumps(ref_json, sort_keys=True, indent=2))

    def test_shadow_attributes_existing(self):
        self.mispevent.load_file('tests/mispevent_testfiles/shadow.json')
        with open('tests/mispevent_testfiles/shadow.json', 'r') as f:
            ref_json = json.load(f)
        self.assertEqual(self.mispevent.to_json(sort_keys=True, indent=2), json.dumps(ref_json, sort_keys=True, indent=2))

    @unittest.skip("Not supported on MISP.")
    def test_shadow_attributes(self):
        self.init_event()
        p = self.mispevent.add_proposal(type='filename', value='baz.jpg')
        del p.uuid
        a = self.mispevent.add_attribute('filename', 'bar.exe')
        del a.uuid
        p = self.mispevent.attributes[0].add_proposal(type='filename', value='bar.pdf')
        del p.uuid
        with open('tests/mispevent_testfiles/proposals.json', 'r') as f:
            ref_json = json.load(f)
        self.assertEqual(self.mispevent.to_json(sort_keys=True, indent=2), json.dumps(ref_json, sort_keys=True, indent=2))

    def test_default_attributes(self):
        self.mispevent.add_object(name='file', strict=True)
        a = self.mispevent.objects[0].add_attribute('filename', value='bar', Tag=[{'name': 'blah'}])
        del a.uuid
        a = self.mispevent.objects[0].add_attribute('pattern-in-file', value='baz')
        self.assertEqual(a.category, 'Artifacts dropped')
        del a.uuid
        self.mispevent.add_object(name='file', strict=False, default_attributes_parameters=self.mispevent.objects[0].attributes[0])
        a = self.mispevent.objects[1].add_attribute('filename', value='baz')
        del a.uuid
        self.mispevent.objects[0].uuid = 'a'
        self.mispevent.objects[1].uuid = 'b'
        with open('tests/mispevent_testfiles/event_obj_def_param.json', 'r') as f:
            ref_json = json.load(f)
        del self.mispevent.uuid
        self.assertEqual(self.mispevent.to_json(sort_keys=True, indent=2), json.dumps(ref_json, sort_keys=True, indent=2))

    def test_obj_default_values(self):
        self.init_event()
        self.mispevent.add_object(name='whois', strict=True)
        a = self.mispevent.objects[0].add_attribute('registrar', value='registar.example.com')
        del a.uuid
        a = self.mispevent.objects[0].add_attribute('domain', value='domain.example.com')
        del a.uuid
        a = self.mispevent.objects[0].add_attribute('nameserver', value='ns1.example.com')
        del a.uuid
        a = self.mispevent.objects[0].add_attribute('nameserver', value='ns2.example.com', disable_correlation=False, to_ids=True, category='External analysis')
        del a.uuid
        self.mispevent.objects[0].uuid = 'a'
        with open('tests/mispevent_testfiles/def_param.json', 'r') as f:
            ref_json = json.load(f)
        del self.mispevent.uuid
        self.assertEqual(self.mispevent.to_json(sort_keys=True, indent=2), json.dumps(ref_json, sort_keys=True, indent=2))

    def test_obj_references_export(self):
        self.init_event()
        obj1 = MISPObject(name="file")
        obj2 = MISPObject(name="url", standalone=False)
        obj1.add_reference(obj2, "downloads")
        obj2.add_reference(obj1, "downloaded-by")
        self.assertFalse("ObjectReference" in obj1.jsonable())
        self.assertTrue("ObjectReference" in obj2.jsonable())
        self.mispevent.add_object(obj1)
        obj2.standalone = True
        self.assertTrue("ObjectReference" in obj1.jsonable())
        self.assertFalse("ObjectReference" in obj2.jsonable())

    def test_event_not_edited(self):
        self.mispevent.load_file('tests/mispevent_testfiles/existing_event.json')
        self.assertFalse(self.mispevent.edited)

    def test_event_edited(self):
        self.mispevent.load_file('tests/mispevent_testfiles/existing_event.json')
        self.mispevent.info = 'blah'
        self.assertTrue(self.mispevent.edited)

    def test_event_tag_edited(self):
        self.mispevent.load_file('tests/mispevent_testfiles/existing_event.json')
        self.assertFalse(self.mispevent.edited)
        self.mispevent.add_tag('foo')
        self.assertTrue(self.mispevent.edited)

    def test_event_attribute_edited(self):
        self.mispevent.load_file('tests/mispevent_testfiles/existing_event.json')
        self.mispevent.attributes[0].value = 'blah'
        self.assertTrue(self.mispevent.attributes[0].edited)
        self.assertFalse(self.mispevent.attributes[1].edited)
        self.assertTrue(self.mispevent.edited)

    def test_event_attribute_tag_edited(self):
        self.mispevent.load_file('tests/mispevent_testfiles/existing_event.json')
        self.assertFalse(self.mispevent.edited)
        self.mispevent.attributes[0].tags[0].name = 'blah'
        self.assertTrue(self.mispevent.attributes[0].tags[0].edited)
        self.assertFalse(self.mispevent.attributes[0].tags[1].edited)
        self.assertTrue(self.mispevent.attributes[0].edited)
        self.assertTrue(self.mispevent.edited)

    def test_event_attribute_tag_edited_second(self):
        self.mispevent.load_file('tests/mispevent_testfiles/existing_event.json')
        self.assertFalse(self.mispevent.edited)
        self.mispevent.attributes[0].add_tag(name='blah')
        self.assertTrue(self.mispevent.attributes[0].edited)
        self.assertTrue(self.mispevent.edited)

    def test_event_object_edited(self):
        self.mispevent.load_file('tests/mispevent_testfiles/existing_event.json')
        self.assertFalse(self.mispevent.edited)
        self.mispevent.objects[0].comment = 'blah'
        self.assertTrue(self.mispevent.objects[0].edited)
        self.assertFalse(self.mispevent.objects[1].edited)
        self.assertTrue(self.mispevent.edited)

    def test_event_object_attribute_edited(self):
        self.mispevent.load_file('tests/mispevent_testfiles/existing_event.json')
        self.assertFalse(self.mispevent.edited)
        self.mispevent.objects[0].attributes[0].comment = 'blah'
        self.assertTrue(self.mispevent.objects[0].attributes[0].edited)
        self.assertTrue(self.mispevent.objects[0].edited)
        self.assertTrue(self.mispevent.edited)

    def test_event_object_attribute_edited_tag(self):
        self.mispevent.load_file('tests/mispevent_testfiles/existing_event.json')
        self.assertFalse(self.mispevent.edited)
        self.mispevent.objects[0].attributes[0].add_tag('blah')
        self.assertTrue(self.mispevent.objects[0].attributes[0].edited)
        self.assertTrue(self.mispevent.objects[0].edited)
        self.assertTrue(self.mispevent.edited)
        with open('tests/mispevent_testfiles/existing_event_edited.json', 'r') as f:
            ref_json = json.load(f)
        self.assertEqual(self.mispevent.to_json(sort_keys=True, indent=2), json.dumps(ref_json, sort_keys=True, indent=2))

    def test_obj_by_id(self):
        self.mispevent.load_file('tests/mispevent_testfiles/existing_event.json')
        misp_obj = self.mispevent.get_object_by_id(1556)
        self.assertEqual(misp_obj.uuid, '5a3cd604-e11c-4de5-bbbf-c170950d210f')

    def test_userdefined_object_custom_template(self):
        self.init_event()
        with open('tests/mispevent_testfiles/test_object_template/definition.json') as f:
            template = json.load(f)
        self.mispevent.add_object(name='test_object_template', strict=True,
                                  misp_objects_template_custom=template)
        with self.assertRaises(InvalidMISPObject) as e:
            # Fail on required
            self.mispevent.to_json(sort_keys=True, indent=2)
        self.assertEqual(e.exception.message, '{\'member3\'} are required.')

        a = self.mispevent.objects[0].add_attribute('member3', value='foo')
        del a.uuid
        with self.assertRaises(InvalidMISPObject) as e:
            # Fail on requiredOneOf
            self.mispevent.to_json(sort_keys=True, indent=2)
        self.assertEqual(e.exception.message, 'At least one of the following attributes is required: member1, member2')

        a = self.mispevent.objects[0].add_attribute('member1', value='bar')
        del a.uuid
        a = self.mispevent.objects[0].add_attribute('member1', value='baz')
        del a.uuid
        with self.assertRaises(InvalidMISPObject) as e:
            # member1 is not a multiple
            self.mispevent.to_json(sort_keys=True, indent=2)
        self.assertEqual(e.exception.message, 'Multiple occurrences of member1 is not allowed')

        self.mispevent.objects[0].attributes = self.mispevent.objects[0].attributes[:2]
        self.mispevent.objects[0].uuid = 'a'
        with open('tests/mispevent_testfiles/misp_custom_obj.json', 'r') as f:
            ref_json = json.load(f)
        del self.mispevent.uuid
        self.assertEqual(self.mispevent.to_json(sort_keys=True, indent=2), json.dumps(ref_json, sort_keys=True, indent=2))

    def test_userdefined_object_custom_dir(self):
        self.init_event()
        self.mispevent.add_object(name='test_object_template', strict=True, misp_objects_path_custom='tests/mispevent_testfiles')
        with self.assertRaises(InvalidMISPObject) as e:
            # Fail on required
            self.mispevent.to_json(sort_keys=True, indent=2)
        self.assertEqual(e.exception.message, '{\'member3\'} are required.')

        a = self.mispevent.objects[0].add_attribute('member3', value='foo')
        del a.uuid
        with self.assertRaises(InvalidMISPObject) as e:
            # Fail on requiredOneOf
            self.mispevent.to_json(sort_keys=True, indent=2)
        self.assertEqual(e.exception.message, 'At least one of the following attributes is required: member1, member2')

        a = self.mispevent.objects[0].add_attribute('member1', value='bar')
        del a.uuid
        a = self.mispevent.objects[0].add_attribute('member1', value='baz')
        del a.uuid
        with self.assertRaises(InvalidMISPObject) as e:
            # member1 is not a multiple
            self.mispevent.to_json(sort_keys=True, indent=2)
        self.assertEqual(e.exception.message, 'Multiple occurrences of member1 is not allowed')

        self.mispevent.objects[0].attributes = self.mispevent.objects[0].attributes[:2]
        self.mispevent.objects[0].uuid = 'a'
        with open('tests/mispevent_testfiles/misp_custom_obj.json', 'r') as f:
            ref_json = json.load(f)
        del self.mispevent.uuid
        self.assertEqual(self.mispevent.to_json(sort_keys=True, indent=2), json.dumps(ref_json, sort_keys=True, indent=2))

    def test_first_last_seen(self):
        me = MISPEvent()
        me.info = 'Test First and Last Seen'
        me.date = '2020.01.12'
        self.assertEqual(me.date.day, 12)
        me.add_attribute('ip-dst', '8.8.8.8', first_seen='06-21-1998', last_seen=1580213607.469571)
        self.assertEqual(me.attributes[0].first_seen.year, 1998)
        self.assertEqual(me.attributes[0].last_seen.year, 2020)
        now = datetime.now().astimezone()
        me.attributes[0].last_seen = now
        today = date.today()
        me.attributes[0].first_seen = today
        self.assertEqual(me.attributes[0].first_seen.year, today.year)
        self.assertEqual(me.attributes[0].last_seen, now)

    def test_feed(self):
        me = MISPEvent()
        me.info = 'Test feed'
        org = MISPOrganisation()
        org.name = 'TestOrg'
        org.uuid = '123478'
        me.Orgc = org
        me.add_attribute('ip-dst', '8.8.8.8')
        obj = me.add_object(name='file')
        obj.add_attributes('filename', *['foo.exe', 'bar.exe'])
        h = hashlib.new('md5')
        h.update(b'8.8.8.8')
        hash_attr_val = h.hexdigest()
        feed = me.to_feed(with_meta=True)
        self.assertEqual(feed['Event']['_hashes'][0], hash_attr_val)
        self.assertEqual(feed['Event']['_manifest'][me.uuid]['info'], 'Test feed')
        self.assertEqual(len(feed['Event']['Object'][0]['Attribute']), 2)

    def test_object_templates(self):
        me = MISPEvent()
        for template in glob.glob(str(me.misp_objects_path / '*' / 'definition.json')):
            with open(template) as f:
                t_json = json.load(f)
                if 'requiredOneOf' in t_json:
                    obj_relations = set(t_json['attributes'].keys())
                    subset = set(t_json['requiredOneOf']).issubset(obj_relations)
                    self.assertTrue(subset, f'{t_json["name"]}')
                if 'required' in t_json:
                    obj_relations = set(t_json['attributes'].keys())
                    subset = set(t_json['required']).issubset(obj_relations)
                    self.assertTrue(subset, f'{t_json["name"]}')
                for obj_relation, entry in t_json['attributes'].items():
                    self.assertTrue(entry['misp-attribute'] in me.describe_types['types'], f'Missing type: {entry["misp-attribute"]}')
                    if 'categories' in entry:
                        subset = set(entry['categories']).issubset(me.describe_types['categories'])
                        self.assertTrue(subset, f'{t_json["name"]} - {obj_relation}')

    def test_git_vuln_finder(self):
        with open('tests/git-vuln-finder-quagga.json') as f:
            dump = json.load(f)

        for vuln in dump.values():
            author = vuln['author']
            vuln_finder = GitVulnFinderObject(vuln)
            self.assertEqual(vuln_finder.get_attributes_by_relation('author')[0].value, author)


if __name__ == '__main__':
    unittest.main()
