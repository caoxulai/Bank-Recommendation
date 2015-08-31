
import timeit
from numpy import genfromtxt

# Initiate tiemr
start = timeit.default_timer()


def construct_training_set(label_after_file_name, data_file_name, target_file_name):

    label_after = genfromtxt(label_after_file_name, delimiter=',')
    f = open(target_file_name, 'w')

    i = 0
    for line in open(data_file_name):
        s = str(int(label_after[i])) + " "

        line = line.split(None, 1)
        # In case an instance with all zero features
        if len(line) == 1: line += ['']
        label, features = line
        s += features
        f.write(s)
        i += 1


construct_training_set('label_after.csv', 'raw_label_feature', 'label_feature')

# svm classifier using libsvm
fr = 8
to = 10

# import sklearn.svm.libsvm as svm
from svmutil import *
y, x = svm_read_problem('label_feature')
print("Problem has been read...")

# m = svm_train(y[fr:to], x[fr:to], '-s 1 -h 0 -m 1000')
mt = svm_train(y, x, '-s 0 -h 0 -m 1000')
print("Model get trained successfully...")
#p_label, p_acc, p_val = svm_predict(y[0:1000] + y[59000:60000], x[0:1000] + x[59000:60000], m)

svm_save_model('svm_model', mt)
m = svm_load_model('svm_model')


# svm_predict(y[fr:to], x[fr:to], m)
yt = y[fr:to] + y[500:505] + y[1500:1502] + y[900:902] + y[2000:2002]
xt = x[fr:to] + x[500:505] + x[1500:1502] + x[900:902] + x[2000:2002]


p_label, p_acc, p_val = svm_predict(yt, xt, m)
print(p_acc)
for i in range(0, len(p_label)):
    print(i, p_label[i], p_val[i])
    print('\n')



# from sklearn.datasets import load_svmlight_file
# from sklearn.naive_bayes import GaussianNB
# x_train, y_train = load_svmlight_file('svm_input')
# gnb = GaussianNB()
# y_pred = gnb.fit(x_train, y_train).predict(x_train)
# print(y_pred)


#Your statements here
stop = timeit.default_timer()

print "Time: " + "{0:.2f}".format(stop - start) + "s"
