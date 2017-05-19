<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Braghim Sistemas
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
namespace SuitUpTest;

use SuitUpTest\Forms\TestForm;

class SuitUp6FormTest extends \PHPUnit_Framework_TestCase
{
  public function testInstance()
  {
    $form = new TestForm();
    $this->assertInstanceof('\SuitUp\FormValidator\Validation', $form);
  }
  
  public function testPostExist()
  {
    $form = new TestForm();
    
    // Force post to test
    $form->post = array(
      'name' => 'Braghim Sistemas',
      'email' => 'braghim.sistemas@gmail.com',
    );
    
    // There is post (forced)
    $this->assertCount(2, $form->post);
    $this->assertInternalType('array', $form->post);
  }
  
  public function testIsValid()
  {
    $formValid = new TestForm();
    
    // Force post to test
    $formValid->post = array(
      'name' => 'Braghim Sistemas',
      'email' => 'braghim.sistemas@gmail.com',
    );
    
    // Must to be valid
    $this->assertTrue($formValid->isValid());
  }
  
  public function testIsInvalid()
  {
    $formInvalid = new TestForm();
    
    // Force post to test
    $formInvalid->post = array(
      'name' => 'Brag',
      'email' => 'braghim.sistemas',
    );
    
    // Must to be invalid
    $this->assertFalse($formInvalid->isValid());
  }
  
  public function testGetData()
  {
    $form = new TestForm();
    
    // Force post to test
    $form->post = array(
      'name' => 'Braghim Sistemas',
      'email' => 'braghim.sistemas@gmail.com',
    );
    
    $data = $form->getData();
    
    // Must contain all post fields and keys created
    $this->assertArrayHasKey('name', $data);
    $this->assertContains('Braghim Sistemas', $data);
    $this->assertArrayHasKey('email', $data);
    $this->assertContains('braghim.sistemas@gmail.com', $data);
  }
  
  public function testAddData()
  {
    $form = new TestForm();
    
    // Values in the data
    $this->assertCount(2, $form->getData()); // Before
    
    $form->addData('test', array(
      'validation' => array('notEmpty'),
      'filter' => array('string'),
    ));
    
    // After validate
    $data = $form->getData();
    $this->assertArrayHasKey('test', $data);
    
    // Ps.: After validate the first time, it's no possible
    // to revalidate the form. See issue #11 on github.
  }
  
  public function testMessages()
  {
    $form = new TestForm();
    
    // Force post to test
    $form->post = array(
      'name' => 'Bra',
      'email' => 'braghim.sistemas',
    );
    $form->isValid();
    $messages = $form->getMessages();
    
    $this->assertArrayHasKey('name', $messages);
    $this->assertArrayHasKey('email', $messages);
    $this->assertCount(1, $messages['name']);
    $this->assertCount(2, $messages['email']);
  }
}



















