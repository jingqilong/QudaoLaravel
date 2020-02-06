# Array Model
  
## 简介

* 你可以这样使用：

```
    ArrayModel::select('a.id','a.name','a.age','b.subject','b.score')
    ->from($student_list, 'a')
    ->join($score_list,'b')
    ->on(['a.id','=','b.student_id')
    ->where(['b.score','>=',60])
    ->get()
    ->toArray();
```

* 你可能要问，$score_array从哪里来？
    你可以这么做：
    
```
    $model = ArrayModel::select('a.id','a.name','a.age','b.exam_date',b.subject','b.score')
                 ->from($src_array, 'a');
    $student_id_array = $model->pluck('a.id');
    //通过以上语句拿到ID，你就可以到数据库里用 select in ()查询了
    //接下来：
    $model->join($score_array,'b')
          ->on(['a.id','=','b.student_id');
    $result = $model->get()->toArray();
    
    //接下来你还可以
    $model->where(['b.score','>=',60])
    $result_a = $model->get()->toArray();
    
    //甚致还可以，
    $model->where(['b.score','>=',0])
    ->orderBy(['a.id','adc'],['b.score','desc'])
    $result_b = $model->get()->toArray();
    
    //甚致还可以,通过闭包回调进行自定义处理
    $result_b = $model->get(
        function($src_item,$join_item){
            $result = $src_item;
            $result['exam_date']= date("Y年m月d日" $join_item['exam_date']);
            $result['subject'] = $join_item['subject'];
            $result['score'] = $join_item['score'];
            return $result;
    })->toArray(); 
    
    
```
  
## 为什么需要它？

### 缘起

* 曾经遇到过一个十年的码农，在foreach内查询数据库。

* 曾遇到过新手程序员，通过嵌套foreach实现两数组的联表。

* 更基础的，多维数组，如何排序与分页？

* 因为以上各类初级的写法，程序效率非常低下。

* Laravel的Elequent ORM中的hasOne,HasMany实际上也是在主表查询结果出来以后，再去查的。效率相当低下。其实，绝大多数的ORM也都是这么做的。所以，这样的查询往往成了系统的效率瓶颈。

* Laravel的Elequent ORM已经把PHP用到极致，可以说是行业内顶级的ORM了。（我敢说，没有人能再写出比此更好的。除非编程语言再改进，那它也会升级）但是，对于多次查询以后再进行联表这一类更深层次的需求还是没有考虑到。实际上是没有考虑到码农的问题。

* 上网寻找同类组件时，只找到了ArrayJoin，可这个组件，几乎与码农写的无差别。同样是嵌套foreach。没有办法，只能自己撸一个了。

### 可能的需求

* 新手程序员经常会在foreach中嵌套数据库查询。使用它，虽然只查了两个表，但数据库只访问了两次。本人就曾遇到10年的老程序员，在foreach中使用循环查数据库。有了它，你就要可以把这个行为禁掉。不会让你Code Review时发疯。

* 大厂都规定，数据库联表查询，不允许超过三个表。有些表，比如，状态表，类型定义，分类定表等表则是需要缓存，并在查询结果出来后进行后续联表操作的。这不只是减轻数据库负担，同时也是让程序更加简洁。

* 有时会遇到外部的导入数据，或者像处理文件目录那样，没有直接分页的。并且，也是要处理的。

* 本组件使用了数组索引的方法提升效率。初级程序员常会把join的表也查出来。再用foreach嵌套，到join的表中找到对应的记录。本组件是通过将join的表用关联字段作为数组的key进行索引。从而使本来是乘法运算次数的，现在变成了加法的次数。比如，原来都是10条记录，那么，foreach嵌套是查询次数最高会是10*10=100次。而在则是 10+10=20次，从而提升了效率。

* 还有一些特殊的报表。比如上例，要查出，60分以上的人，以及总人数，60分以下的，以及总人数，这些操作均可以在程序中一并完成，而不再需要数据库。

### 功能说明

#### 基本功能
   
* 通过PHP实现两个数组的联表查询。
  
* 支持通过selet进行结果字段筛选。  

* 支持：innerJoin, leftJoin

* 支持多种复杂条件筛选。

#### 原理与效率

##### 原理

1、首先通过Pluck在主表中查出要关联的ID。
    
2、到数据库中用select in()一次查出要关联的记录。
    
3、根据关联关系，以关联表的字段用数组的key对关联表数据进行MAP（索引）。
    
4、合并两个数组。
  
##### 效率：

因为，基于索引，所以，不再需要搜索关联表。用key直接检索。
    
由此，原来是10*10次计算，现在则是10+10次计算。效率大大提高。
    
同时，代码也简洁可读。

如果想让你的小伙伴快速开发，且让你不会在Code Review时崩溃，那就赶快用它吧。
 
#### 附加功能
 
二维数组的排序，筛选。 

## 优势

* 使用此组件，你可以规范码农的代码。

* 使用此组件，同时也可以提升PHP程序的效率，避免码农低效的代码。也尽最大可能降低了数组操作的次数

* 使用了逻辑树化简算法对join数组索引进行优化。从而大大提升了运算效率。

## 环境需求：
    
    Php版本 >= `7.2`
    
    本组件未使用任何第三方组件。
    
    推荐集成在Laravel 5.8 及以上的版本中。
    
    当然，像Yii2, Symfony3, WorkerMan3.0，Swoft，FastD等理应都可以集成，如有问题，请在issuse中反馈。
    
    【注：本组件无法及时考虑ThinkPhp的问题！敬请谅解！】

## 安装
    
```
    $ composer require bardoqi\arraymodel:dev
```    
    
## API向导：

### 说明：
    
本组件API均是尽最大可能与Laravel框架的Model保持一致。但由于本组件是操作数组的，不是操作数据库的，所以，像WhereRaw这样的函数就无法实现。（当然也是不无法实现，真的要实现，则要使用AST，即：Abstract Syntax Tree,但若使用了AST，则效率必须会降低。）

### API清单：

#### 静态调用：
  与Laravel的Model一样，你不需要通过new创建对象，直接从静态调用还始。首次函数调用后，即可以用->。  
  例如：    $model = ArrayModel::select('a.id','a.name','a.age','b.exam_date',b.subject','b.score')
                      ->from($src_array, 'a');
#### 基本查询    

基本查询支持字段列表筛选，但是，但目前不支持函数（不在数据库系统中，函数要调用PHP要本身的）

* ArrayModel::select(...$fields)
  参数：$fields 为字段列表清单。格式为 'alias.field1','alias.field2'
  例如：$arrayModel->select('a.id','a.name','a.age','b.subject','b.score')
  默认值:'a.*','b.*',也可以不传。这样，结果中会包括源数据的所有字段。
  
* ArrayModel::from($src_array, $alias)
  参数:$src_array 要处理的主数组，类似数据库的主表。
       $alias 别名，一定要传。因为程序是依据别名进行处理的。
  示例：$arrayModel->from($student, 'a')    
   
#### 关联关系

关联关系支持: innerJoin, leftJoin

* ArrayModel::join($join_array, $alias)
  参数:$join_array 要处理的从数组，类似数据库的从表。
         $alias 别名，一定要传。因为程序是依据别名进行处理的。
  示例：$arrayModel->join($score, 'b') 
  说明：默认是innerJoin
    
* ArrayModel::innerJoin($join_array, $alias)
  参数:$join_array 要处理的从数组，类似数据库的从表。
       $alias 别名，一定要传。因为程序是依据别名进行处理的。
  示例：$arrayModel->innerJoin($score, 'b')
  说明：innerJoin时，从表无记录，则会舍弃主表此条记录。 

* ArrayModel::leftJoin($join_array, $alias)
  参数:$join_array 要处理的从数组，类似数据库的从表。
       $alias 别名，一定要传。因为程序是依据别名进行处理的。
  示例：$arrayModel->leftJoin($score, 'b')
  说明：leftJoin时，从表无记录，则会自动添加空记录。
    

#### 关联条件

本组件的条件关联是通过逻辑树，排序列表，双向链表等协作完成的。所以，效率很高。
可以支持复杂的关联条件，但目前不支持函数（不在数据库系统中，函数要调用PHP要本身的）
  
* ArrayModel::on(...$ons)
  参数：$ons 为联表条件。格式为 ['alias.field1','alias.field2']
  例如：$arrayModel->on(['a.id','b.student_id'])
  默认操作符是"=",如果不是'=',则要传入操作符。
  例如：['a.id','>=','b.student_id']
  条件中支持的运算符有：=,>,<,>=,<=和contains
  但是，我们推荐只使用=和contains(速度快，其它的还是交给数据库去处理)
  什么情况下用contains呢？当你主表中的字段是以逗号分隔的id串时，就要用contains
  比如，主表中有 image_ids, 数据如：'1,3,7,11',从表中有 image_id
  那么条件就应当是，$arrayModel->on(['a.image_ids','contains','b.image_id'])
  on()中一次可以传入多个条件，默认是And关系。
  另外，此函数的参数还可以传入闭包，参见下面：on()与闭包。
  注：关联条件并不在意你是一对一，还是一对多。但如果是一对多，则结果会是多条记录。
  
* ArrayModel::orOn($on)
  参数：$on 为联表条件。格式为 ['alias.field1','alias.field2']
  不同于上述的on(),这里只能传入一个条件，但是，逻辑关系是or.
  例如：$arrayModel->orOn(['a.image_ids','contains','b.image_id'])
  
* ArrayModel::andOn($on)
  参数：$on 为联表条件。格式为 ['alias.field1','alias.field2']
  不同于上述的on(),这里只能传入一个条件，但是，逻辑关系是and.
  例如：$arrayModel->andOn(['a.image_ids','contains','b.image_id'])

* on()与闭包
  通过闭包，可以实现复杂的关联条件。
  例如:
  on(function($query){
    return $query->on(['a.gender','b.gender'])
      ->andOn(['a.subject_id','b.subject_id']);
  })
  那么，以上就是 or(('a.gender'='b.gender')and('a.subject_id'='b.subject_id'))
   
* ArrayModel::onContains($on)
  参数：$on 为联表条件。格式为['a.image_ids','b.image_id']
  不同于上述的on(),这里只能传入一个条件，但是，逻辑关系是and.
  这里，操作符即是：contains. 这个操作符，即是指主表中是用逗号分隔的ID串。
  我们当然不推荐这么做。但如果这么做了，这个操作符还是相当有用的。
  例如：$arrayModel->onContains(['a.image_ids','b.image_id'])
  
#### Where筛选
 
  本组件的筛选是记录进行关联的循环中进行筛选的。因而速度快于其它方式。
  接口函数有：
  where，orWhere，whereIn，orWhereIn，whereNotIn，orWhereNotIn 
  whereIs，orWhereIs， whereIsNot，orWhereIsNot
  whereBetween，orWhereBetween
  但是，没有whereRaw这一系列的。因为，如果是查数据库，则语句可以直接嵌入，
  而对于数组，这里不好解析成程序。
    
* ArrayModel::where(...$wheres)
  参数：$wheres 为筛选条件。格式为 ['alias.field','value']
  例如：$arrayModel->where(['a.id',3])
  默认操作符是"=",如果不是'=',则要传入操作符。
  例如：['a.id','>=',20]
  条件中支持的运算符有：=,>,<,>=,<=,另外，还通过其它函数提供了其它的操作符。
  参见下面的内容。
  where(...$wheres)可以传入任意多个筛选条件，默认逻辑关系是and
  同样，与on()关联条件一样，where(...$wheres)也支持闭包，可以让你进行复杂的筛选。
  参见下面的where()与闭包。
  
* ArrayModel::orWhere($where)
  参数：$where 为筛选条件。格式为 ['alias.field','value']
  例如：$arrayModel->orWhere(['a.id',3])
  默认操作符是"=",如果不是'=',则要传入操作符。
  例如：['a.id','>=',20]
  这里不同用where(), orWhere($where)仅支持传入一个查询条件。
  条件中支持的运算符有：=,>,<,>=,<=,另外，还通过其它函数提供了其它的操作符。
  参见下面的内容。

* ArrayModel::whereIn($where)
  参数：$where 为筛选条件。格式为 ['alias.field','value']
  例如：$arrayModel->whereIn(['a.id','20,13,23,15'])
  操作符是"in"  例如：['a.id','20,13,23,15']
  这里不同where(), whereIn($where)仅支持传入一个查询条件。
  逻辑运算符是and
  
* ArrayModel::orWhereIn($where)
  参数：$where 为筛选条件。格式为 ['alias.field','value']
  例如：$arrayModel->orWhereIn(['a.id','20,13,23,15'])
  操作符是"in"  例如：['a.id','20,13,23,15']
  这里不同where(), orWhereIn($where)仅支持传入一个查询条件。
  逻辑运算符是Or 
  
* ArrayModel::whereNotIn($where)
  参数：$where 为筛选条件。格式为 ['alias.field','value']
  例如：$arrayModel->whereNotIn(['a.id','20,13,23,15'])
  操作符是"NotIn"  例如：['a.id','20,13,23,15']
  这里不同where(), whereNotIn($where)仅支持传入一个查询条件。
  逻辑运算符是and
  
* ArrayModel::orWhereNotIn($where)
  参数：$where 为筛选条件。格式为 ['alias.field','value']
  例如：$arrayModel->orWhereNotIn(['a.id','20,13,23,15'])
  操作符是"NotIn"  例如：['a.id','20,13,23,15']
  这里不同where(), orWhereNotIn($where)仅支持传入一个查询条件。
  逻辑运算符是Or 

* ArrayModel::whereBetween($where)
  参数：$where 为筛选条件。格式为 ['alias.field','minvalue','maxvalue']
  例如：$arrayModel->whereBetween(['a.id','15','30'])
  操作符是"Between"  例如：['a.id','15','30']
  这里不同where(), whereBetween($where)仅支持传入一个查询条件。
  逻辑运算符是And

* ArrayModel::orWhereBetween($where)
  参数：$where 为筛选条件。格式为 ['alias.field','minvalue','maxvalue']
  例如：$arrayModel->orWhereBetween(['a.id','15','30'])
  操作符是"Between"  例如：['a.id','15','30']
  这里不同where(), orWhereBetween($where)仅支持传入一个查询条件。
  逻辑运算符是Or

* ArrayModel::whereIs($where)
  参数：$where 为筛选条件。格式为 ['alias.field','value']
  例如：$arrayModel->whereIs(['a.id',null])
  操作符是"is"  例如：['a.id',null]
  这里不同where(), whereIs($where)仅支持传入一个查询条件。
  逻辑运算符是And   
    
* ArrayModel::orWhereIs($where)
  参数：$where 为筛选条件。格式为 ['alias.field','value']
  例如：$arrayModel->orWhereIs(['a.id',null])
  操作符是"is"  例如：['a.id',null]
  这里不同where(), orWhereIs($where)仅支持传入一个查询条件。
  逻辑运算符是Or  
  
* ArrayModel::whereIsNot($where)
  参数：$where 为筛选条件。格式为 ['alias.field','value']
  例如：$arrayModel->whereIsNot(['a.id',null])
  操作符是"isNot"  例如：['a.id',null]
  这里不同where(), whereIsNot($where)仅支持传入一个查询条件。
  逻辑运算符是And 
    
* ArrayModel::orWhereIsNot($where)
  参数：$where 为筛选条件。格式为 ['alias.field','value']
  例如：$arrayModel->orWhereIsNot(['a.id',null])
  操作符是"isNot"  例如：['a.id',null]
  这里不同where(), orWhereIsNot($where)仅支持传入一个查询条件。
  逻辑运算符是Or      
        
* where()与闭包
  通过闭包以及括号，可以实现复杂的关联条件。
  例如:
  $arrayModel->where(['a.id'='1'])
    ->where(function($query){
    return $query 
      ->orWhere(['a.gender','2'])
      ->where(['a.subject_id','3']);
  })
  那么，以上就是 where(('a.id'='1') and (('a.gender'='2')or('a.subject_id'='3')))
   
#### 排序
  
  本组件提供了排序功能。可以多字段排序。

* ArrayModel::orderBy(...$order_bys)
  参数：$order_bys 形式如：['a.id','desc']
  例如：$arrayModel->orWhere(['a.id','desc'])
  参数可以传任意多个，比如：$arrayModel->orWhere(['a.id','desc'],['b.score','asc'])
  
### 获取结果

* ArrayModel::get($closure)
  参数：$closure，可以不传。但如果有特别的需求，那可以通过此闭包实现在同一循环中奖其它需求处理完成。
  比如联表后，要将状态数字转换为中文的状态名称。或者把日期格式转换一下。
  闭包示例：
  $arrayModel->get(function($left_item,$right_item){
       $left_item['created_at] = date('Y年m月d日'，$right_item['created_at']);
       $left_item['score] = $right_item['score'];
       return $left_item;
    });
  闭包包括两个参数，第一个是主表记录，第二个则是关联的从表的记录。
  这就是说，如果你没有JOIN，那么，此闭包就只有一个参数。
  注意：一旦使用闭包，那么，主从表记录合并的方式必须由你全部在闭包中完成。
  
* ArrayModel::toArray()
  参数：无
  通过get或execute得到的结果是一个QueryBuilder对象。基类是SortedList。
  因为有数组访问接口，所以，可以完全当成数组来用。
  但是，如果你要json_encode，或者只想用数组，那就可以用此函数转换。
  
#### 开发调试
  
  在开发时，可以通过 toSql()进行调试，程序会打出你的sql语句。
  需要注意的是：->get()前与->get()后打出的Sql语句可能不会相同。
  区别一般仅在于on关联第条件的不同。
  但如果说->get()后没有SQL语句，则可能是所用对象不对。
  因为，->get()运行后，返回了一个新的queryBuilder对象，而不是ArrayModel对象了。
  所以，要想看到->get()运行后的SQL，则要把ArrayModel对象先存下来。
    
## 相关不足

* 关联条件：虽然有逻辑树优化算法，但是，只做了交换律与结合律，逻辑算法共有10个运算律（不清楚的，可以上网查一下），但考虑到，如果这些算法都实现，那么，得不偿失。因为，本来是为了提升效率的。但过于复杂的算法会使效率降低。所以，如果你SQL中的on关联条件很复杂，那建议你还是在数据库中完成。（当然，如果可能，还是优化一下你的数据表。）所以，建议在使用时，尽量不要让逻辑树去跑化简程序，虽说对效率影响不大。具体怎么做呢？其实，只要了解交换律与结合律，将AND与OR合并到一起即可。

* 关联条件：目前只有使用"="和"contains"这两个运算符的效率最高。所以，复杂关联建议还是通过视图在数据库中完成。

* where条件。因为无法判筛选后关联和先关联后筛选哪一个效率更高。（实际上取决于记录的数量，记录数越多，先筛选会越好），现在使用的是先关联后筛选的算法。因为考虑到如果页面显示，一般10到20条每页。

* 效率问题：【重要】绝大多数效率问题都是因为关联条件，如果是外键与主键关联，一般没有效率问题。如果关联的不是主键，那从表关联字段需要添加索引。

## 路线图

* 目前暂没有升级计划。以下可能是要考虑升级的内容。

* 1、分页支持。

* 2、字段列表函数支持。比如：增加date函数，则可以自动转换成确定的日期格式。

* 3、自定义函数表支持。通过自定义函数表，从而可以让用户少写一些闭包。更方便使用。

## 版权与授权

### 版权
  
    CopyRight: Bardo QI 
    Email: 67158925@qq.com
  
### 授权
  
    MIT license
    
## 技术支持
   
    QQ: 67158925
    
## 捐赠
    
如果你觉得本组件好，希望能持续支持与改进，请您帮我买杯咖啡！
    
    
